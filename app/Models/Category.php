<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $guarded = [];

    public function guest()
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }
}
