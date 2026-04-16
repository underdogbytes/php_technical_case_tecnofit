<?php
declare(strict_types=1);

namespace App\Application\DTO;

class RequestWithdrawalDTO
{
    public function __construct(
        public string $accountId,
        public string $method,
        public array $pixData,
        public float $amount,
        public ?string $scheduledFor = null
    ) {}
}
