<?php
declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\AccountWithdraw;

interface NotificationServiceInterface
{
    public function sendWithdrawNotification(
        AccountWithdraw $withdraw, 
        string $recipientEmail, 
        string $senderEmail
    ): void;
}