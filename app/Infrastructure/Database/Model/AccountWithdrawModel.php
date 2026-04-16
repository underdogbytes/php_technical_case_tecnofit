<?php
declare(strict_types=1);

namespace App\Infrastructure\Database\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\HasOne;

class AccountWithdrawModel extends Model
{
    protected ?string $table = 'account_withdraw';
    protected array $fillable = [
        'id',
        'account_id',
        'method',
        'amount',
        'scheduled',
        'scheduled_for',
        'done',
        'error',
        'error_reason'
    ];
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected array $casts = [
        'amount' => 'float',
        'scheduled' => 'boolean',
        'done' => 'boolean',
        'error' => 'boolean',
    ];

    public function pix(): HasOne
    {
        return $this->hasOne(AccountWithdrawPixModel::class, 'account_withdraw_id', 'id');
    }
}