<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\WithdrawRepositoryInterface;
use App\Domain\Exception\InsufficientBalanceException;

use Hyperf\AsyncQueue\Driver\DriverFactory;

class ProcessScheduledWithdrawalsUseCase
{
    private \Hyperf\AsyncQueue\Driver\DriverInterface $queueDriver;

    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        DriverFactory $driverFactory
    ) {
        $this->queueDriver = $driverFactory->get('default');
    }

    public function execute(): void
    {
        $now = new \DateTimeImmutable();
        $pendings = $this->withdrawRepository->getPendingScheduledWithdrawals($now);

        foreach ($pendings as $withdraw) {
            try {
                $account = $this->accountRepository->findById($withdraw->getAccountId());
                if (!$account) {
                    throw new \Exception("Conta não encontrada");
                }

                $pixData = $this->withdrawRepository->getPixDetails($withdraw->getId());
                $recipientEmail = $pixData['key'] ?? $account->getEmail();

                $account->deduct($withdraw->getAmount());
                $withdraw->complete();

                $this->accountRepository->save($account);
                $this->withdrawRepository->save($withdraw);

                // Despacha email pra liberar CRON
                $job = new \App\Application\Job\SendWithdrawEmailJob(
                    $withdraw->getId(),
                    $account->getId(),
                    $withdraw->getMethod(),
                    $withdraw->getAmount(),
                    $recipientEmail,
                    $account->getEmail()
                );
                $this->queueDriver->push($job);

            } catch (InsufficientBalanceException $e) {
                $withdraw->fail("Saldo insuficiente");
                $this->withdrawRepository->save($withdraw);
            } catch (\Exception $e) {
                $withdraw->fail($e->getMessage());
                $this->withdrawRepository->save($withdraw);
            }
        }
    }
}