<?php
declare(strict_types=1);

namespace App\Infrastructure\Database\Model;

use Hyperf\DbConnection\Model\Model;

class AccountWithdrawPixModel extends Model
{
    protected ?string $table = 'account_withdraw_pix';
    protected array $fillable = ['account_withdraw_id', 'type', 'key'];
    protected string $keyType = 'string';
    protected string $primaryKey = 'account_withdraw_id';
    public bool $incrementing = false;
}
