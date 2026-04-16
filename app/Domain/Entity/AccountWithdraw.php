<?php
namespace App\Domain\Entity;

class AccountWithdraw
{
    private string $id;
    private string $accountId;
    private string $method;
    private float $amount;
    private bool $scheduled;
    private ?string $scheduledFor;
    private bool $done;
    private bool $error;
    private ?string $errorReason;

    /** @var array<string, string> */
    private array $pixData;

    public function __construct(
        string $id,
        string $accountId,
        string $method,
        float $amount,
        bool $scheduled,
        ?string $scheduledFor = null
    ) {
        $this->id = $id;
        $this->accountId = $accountId;
        $this->method = $method;
        $this->amount = $amount;
        $this->scheduled = $scheduled;
        $this->scheduledFor = $scheduledFor;
        $this->done = false;
        $this->error = false;
        $this->errorReason = null;
        $this->pixData = [];
    }

    public function setPixData(string $type, string $key): void
    {
        $this->pixData = ['type' => $type, 'key' => $key];
    }

    public function getPixData(): array
    {
        return $this->pixData;
    }

    public function getId(): string { return $this->id; }
    public function getAccountId(): string { return $this->accountId; }
    public function getAmount(): float { return $this->amount; }
    public function isScheduled(): bool { return $this->scheduled; }
    public function getMethod(): string { return $this->method; }
    public function getScheduledFor(): ?string { return $this->scheduledFor; }
    public function isDone(): bool { return $this->done; }
    public function hasError(): bool { return $this->error; }
    public function getErrorReason(): ?string { return $this->errorReason; }

    public function complete(): void
    {
        $this->done = true;
        $this->error = false;
        $this->errorReason = null;
    }

    public function fail(string $reason): void
    {
        $this->done = true;
        $this->error = true;
        $this->errorReason = $reason;
    }
}
