<?php
declare(strict_types=1);

namespace App\Infrastructure\Email;

use App\Application\Service\NotificationServiceInterface;
use App\Domain\Entity\AccountWithdraw;

class MailhogNotificationService implements NotificationServiceInterface
{
    public function __construct(private string $host = 'mailhog', private int $port = 1025) {}

    public function sendWithdrawNotification(AccountWithdraw $withdraw, string $recipientEmail, string $senderEmail): void
    {
        $fp = fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$fp) {
            return;
        }


        $amountFormatted = number_format($withdraw->getAmount(), 2, ',', '.');
        $dateFormatted = date('d/m/Y H:i:s');

        $body = "Comprovante de Saque Efetuado\r\n";
        $body .= "-----------------------------------\r\n";
        $body .= "Valor: R$ {$amountFormatted}\r\n";
        $body .= "Data e Hora: {$dateFormatted}\r\n";
        $body .= "Método: PIX\r\n";
        $body .= "Enviado por: {$senderEmail}\r\n";
        $body .= "Recebido por: {$recipientEmail}\r\n";
        $body .= "-----------------------------------\r\n";
        $body .= "Este é um e-mail automático de confirmação.";

        fwrite($fp, "EHLO localhost\r\n");
        fread($fp, 512);
        fwrite($fp, "MAIL FROM:<noreply@tecnofit.com>\r\n");
        fread($fp, 512);
        fwrite($fp, "RCPT TO:<{$recipientEmail}>\r\n");
        fread($fp, 512);
        fwrite($fp, "DATA\r\n");
        fread($fp, 512);
        fwrite($fp, "Assunto: Comprovante de Saque PIX\r\n");
        fwrite($fp, "To: {$recipientEmail}\r\n");
        fwrite($fp, "\r\n");
        fwrite($fp, "{$body}\r\n");
        fwrite($fp, ".\r\n");
        fread($fp, 512);
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
    }
}
