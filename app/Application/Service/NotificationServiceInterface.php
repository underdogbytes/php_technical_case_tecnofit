<?php
namespace App\Application\Service;

use App\Domain\Entity\AccountWithdraw;

interface NotificationServiceInterface
{
    public function sendWithdrawNotification(AccountWithdraw $withdraw, string $accountEmail): void;
}
