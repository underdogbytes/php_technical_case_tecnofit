<?php

declare(strict_types=1);

namespace App\Application\Job;

use App\Application\Service\NotificationServiceInterface;
use App\Domain\Entity\AccountWithdraw;
use Hyperf\AsyncQueue\Job;
use Psr\Container\ContainerInterface;

class SendWithdrawEmailJob extends Job
{
    private string $withdrawId;
    private string $accountId;
    private string $method;
    private float $amount;
    private ?string $accountEmail;

    public function __construct(
        string $withdrawId,
        string $accountId,
        string $method,
        float $amount,
        ?string $accountEmail
    ) {
        $this->withdrawId = $withdrawId;
        $this->accountId = $accountId;
        $this->method = $method;
        $this->amount = $amount;
        $this->accountEmail = $accountEmail;
    }

    public function handle(): void
    {
        $container = \Hyperf\Context\ApplicationContext::getContainer();
        $logger = $container->get(\Hyperf\Logger\LoggerFactory::class)->get('withdraw');

        if (empty($this->accountEmail)) {
            $logger->warning("Falha ao enviar recibo: Usuário ID {$this->accountId} não possui e-mail cadastrado.");
            return;
        }

        $notificationService = $container->get(NotificationServiceInterface::class);

        $fakeWithdraw = new AccountWithdraw(
            $this->withdrawId,
            $this->accountId,
            $this->method,
            $this->amount,
            false
        );

        $notificationService->sendWithdrawNotification($fakeWithdraw, $this->accountEmail);
    }
}
