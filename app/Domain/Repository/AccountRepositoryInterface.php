<?php
namespace App\Domain\Repository;

use App\Domain\Entity\Account;

interface AccountRepositoryInterface
{
    public function findById(string $id): ?Account;
    public function findByIdForUpdate(string $id): ?Account;
    public function save(Account $account): void;
}
