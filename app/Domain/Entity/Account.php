<?php
namespace App\Domain\Entity;

use App\Domain\Exception\InsufficientBalanceException;

class Account
{
    private string $id;
    private string $name;
    private ?string $email;
    private float $balance;

    public function __construct(string $id, string $name, ?string $email, float $balance)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->balance = $balance;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function deduct(float $amount): void
    {
        if ($amount > $this->balance) {
            throw new InsufficientBalanceException();
        }
        $this->balance -= $amount;
    }
}
