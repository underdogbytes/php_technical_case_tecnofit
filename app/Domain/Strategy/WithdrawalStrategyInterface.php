<?php

namespace App\Domain\Strategy;

interface WithdrawalStrategyInterface
{
  /**
  * Return method name (ex> PIX, TED)
  */
  public function getMethodName(): string;

  /**
  * Save specific method data in the database
  */
  public function saveDetails(string $withdrawId, array $data): void;
}