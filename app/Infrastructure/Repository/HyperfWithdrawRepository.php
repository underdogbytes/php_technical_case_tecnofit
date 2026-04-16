<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\AccountWithdraw;
use App\Domain\Repository\WithdrawRepositoryInterface;
use App\Infrastructure\Database\Model\AccountWithdrawModel;
use App\Infrastructure\Database\Model\AccountWithdrawPixModel;
use Hyperf\DbConnection\Db;

class HyperfWithdrawRepository implements WithdrawRepositoryInterface
{
    public function save(AccountWithdraw $withdraw): void
    {
        Db::transaction(function () use ($withdraw) {
            AccountWithdrawModel::updateOrCreate(
                ['id' => $withdraw->getId()],
                [
                    'account_id' => $withdraw->getAccountId(),
                    'method' => $withdraw->getMethod(),
                    'amount' => $withdraw->getAmount(),
                    'scheduled' => $withdraw->isScheduled(),
                    'scheduled_for' => $withdraw->getScheduledFor(),
                    'done' => $withdraw->isDone(),
                    'error' => $withdraw->hasError(),
                    'error_reason' => $withdraw->getErrorReason(),
                ]
            );

            if ($withdraw->getMethod() === 'PIX' && !empty($withdraw->getPixData())) {
                $pixData = $withdraw->getPixData();
                AccountWithdrawPixModel::updateOrCreate(
                    ['account_withdraw_id' => $withdraw->getId()],
                    [
                        'type' => $pixData['type'],
                        'key' => $pixData['key'],
                    ]
                );
            }
        });
    }

    public function getPendingScheduledWithdrawals(\DateTimeInterface $now): array
    {
        $models = AccountWithdrawModel::with('pix')
            ->where('scheduled', true)
            ->where('done', false)
            ->where('scheduled_for', '<=', $now->format('Y-m-d H:i:s'))
            ->get();

        $entities = [];
        foreach ($models as $model) {
            $entity = new AccountWithdraw(
                $model->id,
                $model->account_id,
                $model->method,
                (float)$model->amount,
                $model->scheduled,
                $model->scheduled_for
            );
            
            if ($model->done) {
                $entity->complete();
            }
            if ($model->error) {
                $entity->fail($model->error_reason ?? 'unknown error');
            }

            if ($model->pix) {
                $entity->setPixData($model->pix->type, $model->pix->key);
            }
            $entities[] = $entity;
        }

        return $entities;
    }
}
