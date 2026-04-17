<?php
namespace App\Domain\Strategy;

use App\Infrastructure\Database\Model\AccountWithdrawPixModel;

class PixWithdrawalStrategy implements WithdrawalStrategyInterface
{
  public function getMethodName(): string
  {
    return 'PIX';
  }

  public function saveDetails(string $withdrawId, array $data): void
  {
    AccountWithdrawPixModel::create([
      'account_withdraw_id' => $withdrawId,
      'type' => $data['type'],
      'key' => $data['key']
    ]);
  }
}