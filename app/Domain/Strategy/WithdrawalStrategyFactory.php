<?php
namespace App\Domain\Strategy;

class WithdrawalStrategyFactory
{
    private array $strategies;

    public function __construct(PixWithdrawalStrategy $pixStrategy)
    {
        $this->strategies = [
            $pixStrategy->getMethodName() => $pixStrategy,
        ];
    }

    public function make(string $method): WithdrawalStrategyInterface
    {
        if (!isset($this->strategies[$method])) {
            throw new \Exception("Método de saque {$method} não suportado.");
        }

        return $this->strategies[$method];
    }
}