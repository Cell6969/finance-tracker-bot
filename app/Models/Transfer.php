<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    protected $table = 'transfers';
    protected $guarded = [];

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from_account_id', 'id');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to_account_id', 'id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
