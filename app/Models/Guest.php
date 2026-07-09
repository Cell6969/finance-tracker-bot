<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    protected $table = 'guests';

    protected $guarded = [];

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'guest_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'guest_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'guest_id', 'id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'guest_id', 'id');
    }

    public function telegramSession(): HasOne
    {
        return $this->hasOne(TelegramSession::class, 'guest_id', 'id');
    }
}
