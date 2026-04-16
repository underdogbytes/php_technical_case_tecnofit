<?php
namespace App\Domain\Repository;

use App\Domain\Entity\AccountWithdraw;

interface WithdrawRepositoryInterface
{
    public function save(AccountWithdraw $withdraw): void;
    
    /**
     * @param \DateTimeInterface $now
     * @return AccountWithdraw[]
     */
    public function getPendingScheduledWithdrawals(\DateTimeInterface $now): array;
}
