<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\RequestWithdrawalDTO;
use App\Domain\Entity\AccountWithdraw;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\WithdrawRepositoryInterface;
use Ramsey\Uuid\Uuid;

use Hyperf\AsyncQueue\Driver\DriverFactory;

class RequestWithdrawalUseCase
{
    private \Hyperf\AsyncQueue\Driver\DriverInterface $queueDriver;

    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        DriverFactory $driverFactory
    ) {
        $this->queueDriver = $driverFactory->get('default');
    }

    public function execute(RequestWithdrawalDTO $dto): array
    {
        $account = $this->accountRepository->findById($dto->accountId);
        if (!$account) {
            throw new \Exception("Conta não encontrada", 404);
        }

        $withdraw = new AccountWithdraw(
            Uuid::uuid4()->toString(),
            $account->getId(),
            $dto->method,
            $dto->amount,
            $dto->scheduledFor !== null,
            $dto->scheduledFor
        );
        $withdraw->setPixData($dto->pixData['type'], $dto->pixData['key']);

        if (!$withdraw->isScheduled()) {
            // Saque imediato
            $account->deduct($dto->amount);
            $withdraw->complete();

            $this->accountRepository->save($account);
            $this->withdrawRepository->save($withdraw);

            // Redis: assíncrono pra fila
            $job = new \App\Application\Job\SendWithdrawEmailJob(
                $withdraw->getId(),
                $account->getId(),
                $dto->method,
                $dto->amount,
                $account->getEmail()
            );
            $this->queueDriver->push($job);
        } else {
            // Saque agendado
            $this->withdrawRepository->save($withdraw);
        }

        return [
            'withdraw_id' => $withdraw->getId(),
            'scheduled' => $withdraw->isScheduled(),
            'status' => $withdraw->isDone() ? 'completed' : 'pending'
        ];
    }
}