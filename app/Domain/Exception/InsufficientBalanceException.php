<?php
namespace App\Domain\Exception;

class InsufficientBalanceException extends \Exception
{
    public function __construct(string $message = "Saldo insuficiente para o saque solicitado.", int $code = 400, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
