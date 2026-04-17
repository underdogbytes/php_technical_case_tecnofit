<?php

declare(strict_types=1);

namespace App\Application\Job;

use App\Application\Service\NotificationServiceInterface;
use App\Domain\Entity\AccountWithdraw;
use Hyperf\AsyncQueue\Job;

class SendWithdrawEmailJob extends Job
{
    public function __construct(
        private string $withdrawId,
        private string $accountId,
        private string $method,
        private float $amount,
        private string $recipientEmail,
        private ?string $accountEmail
    ) {}

    public function handle(): void
    {
        $container = \Hyperf\Context\ApplicationContext::getContainer();
        $logger = $container->get(\Hyperf\Logger\LoggerFactory::class)->get('withdraw');

        $notificationService = $container->get(NotificationServiceInterface::class);

        $fakeWithdraw = new AccountWithdraw(
            $this->withdrawId,
            $this->accountId,
            $this->method,
            $this->amount,
            false
        );

        $sender = $this->accountEmail ?? 'E-mail não cadastrado';

        $notificationService->sendWithdrawNotification(
            $fakeWithdraw, 
            $this->recipientEmail, 
            $sender
        );
    }
}