<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramSession extends Model
{
    protected $table = 'telegram_sessions';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id', 'id');
    }
}
