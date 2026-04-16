<?php
declare(strict_types=1);

namespace App\Infrastructure\Email;

use App\Application\Service\NotificationServiceInterface;
use App\Domain\Entity\AccountWithdraw;

class MailhogNotificationService implements NotificationServiceInterface
{
    public function __construct(private string $host = 'mailhog', private int $port = 1025) {}

    public function sendWithdrawNotification(AccountWithdraw $withdraw, string $accountEmail): void
    {
        $fp = fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$fp) {
            return;
        }

        $email = $accountEmail;

        $body = "Seu saque de R$ " . number_format($withdraw->getAmount(), 2, ',', '.') . " foi efetuado via PIX.\nData: " . date('Y-m-d H:i:s');

        fwrite($fp, "EHLO localhost\r\n");
        fread($fp, 512);
        fwrite($fp, "MAIL FROM:<noreply@tecnofit.com>\r\n");
        fread($fp, 512);
        fwrite($fp, "RCPT TO:<{$email}>\r\n");
        fread($fp, 512);
        fwrite($fp, "DATA\r\n");
        fread($fp, 512);
        fwrite($fp, "Assunto: Comprovante de Saque PIX\r\n");
        fwrite($fp, "To: {$email}\r\n");
        fwrite($fp, "\r\n");
        fwrite($fp, "{$body}\r\n");
        fwrite($fp, ".\r\n");
        fread($fp, 512);
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
    }
}
