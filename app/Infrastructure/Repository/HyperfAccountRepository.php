<?php
declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Entity\Account;
use App\Domain\Repository\AccountRepositoryInterface;
use App\Infrastructure\Database\Model\AccountModel;

class HyperfAccountRepository implements AccountRepositoryInterface
{
    public function findById(string $id): ?Account
    {
        $model = AccountModel::find($id);
        if (!$model) {
            return null;
        }

        return new Account($model->id, $model->name, $model->email, (float)$model->balance);
    }

    public function findByIdForUpdate(string $id): ?Account
    {
        $model = AccountModel::where('id', $id)->lockForUpdate()->first();
        if (!$model) return null;

        return new Account($model->id, $model->name, $model->email, (float)$model->balance);
    }

    public function save(Account $account): void
    {
        AccountModel::updateOrCreate(
            ['id' => $account->getId()],
            [
                'name' => $account->getName(),
                'email' => $account->getEmail(),
                'balance' => $account->getBalance(),
            ]
        );
    }
}