<?php

namespace App\Models;

use App\Enums\WalletType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $table = 'accounts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'account_id', 'id');
    }

    public function fromTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_account_id', 'id');
    }

    public function toTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_account_id', 'id');
    }

    public function typeLabel(): string
    {
        return WalletType::tryFrom($this->type)?->label() ?? $this->type;
    }
}
