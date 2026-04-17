<?php
declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\DTO\RequestWithdrawalDTO;
use App\Domain\Entity\AccountWithdraw;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Domain\Repository\WithdrawRepositoryInterface;
use App\Domain\Strategy\WithdrawalStrategyFactory;
use Hyperf\DbConnection\Db;
use Ramsey\Uuid\Uuid;
use Hyperf\AsyncQueue\Driver\DriverFactory;

class RequestWithdrawalUseCase
{
    private \Hyperf\AsyncQueue\Driver\DriverInterface $queueDriver;

    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private WithdrawRepositoryInterface $withdrawRepository,
        private WithdrawalStrategyFactory $strategyFactory,
        DriverFactory $driverFactory
    ) {
        $this->queueDriver = $driverFactory->get('default');
    }

    public function execute(RequestWithdrawalDTO $dto): array
    {
        $jobData = null;

        $result = Db::transaction(function () use ($dto, &$jobData) {
            $strategy = $this->strategyFactory->make($dto->method);
            $account = $this->accountRepository->findByIdForUpdate($dto->accountId);
            
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

            $this->withdrawRepository->save($withdraw);
            $strategy->saveDetails($withdraw->getId(), $dto->pixData);

            if (!$withdraw->isScheduled()) {
                $account->deduct($dto->amount);
                $withdraw->complete();

                $this->accountRepository->save($account);
                $this->withdrawRepository->save($withdraw);

                $jobData = [
                    'withdraw_id' => $withdraw->getId(),
                    'account_id' => $account->getId(),
                    'method' => $dto->method,
                    'amount' => $dto->amount,
                    'email' => $account->getEmail()
                ];
            }

            return [
                'withdraw_id' => $withdraw->getId(),
                'scheduled' => $withdraw->isScheduled(),
                'status' => $withdraw->isDone() ? 'completed' : 'pending'
            ];
        });

        if ($jobData !== null) {
            $job = new \App\Application\Job\SendWithdrawEmailJob(
                $jobData['withdraw_id'],
                $jobData['account_id'],
                $jobData['method'],
                $jobData['amount'],
                $jobData['email']
            );
            $this->queueDriver->push($job);
        }

        return $result;
    }
}