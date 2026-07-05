<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use SoftDeletes;

    protected $table = 'guests';
    protected $guarded = [];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'guest_id', 'id');
    }

    public function categories()
    {
        return $this->hasMany(Category::class, 'guest_id', 'id');
    }
}
