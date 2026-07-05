<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $guarded = [];

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function transfer()
    {
        return $this->hasOne(Transfer::class, 'transaction_id', 'id');
    }
}
