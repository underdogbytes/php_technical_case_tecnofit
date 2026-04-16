<?php
declare(strict_types=1);

namespace App\Infrastructure\Database\Model;

use Hyperf\DbConnection\Model\Model;

class AccountModel extends Model
{
    protected ?string $table = 'account';
    protected array $fillable = ['id', 'name', 'email', 'balance'];
    protected string $keyType = 'string';
    public bool $incrementing = false;
}
