<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
return [
    \App\Domain\Repository\AccountRepositoryInterface::class => \App\Infrastructure\Repository\HyperfAccountRepository::class,
    \App\Domain\Repository\WithdrawRepositoryInterface::class => \App\Infrastructure\Repository\HyperfWithdrawRepository::class,
    \App\Application\Service\NotificationServiceInterface::class => \App\Infrastructure\Email\MailhogNotificationService::class,
];
