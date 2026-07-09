<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_at' => 'date',
        ];
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function transfer(): HasOne
    {
        return $this->hasOne(Transfer::class, 'transaction_id', 'id');
    }

    public function typeLabel(): string
    {
        return TransactionType::tryFrom($this->type)?->label() ?? $this->type;
    }

    public function typeIcon(): string
    {
        return TransactionType::tryFrom($this->type)?->icon() ?? '';
    }
}
