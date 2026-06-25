<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'thread_id',
    'sender_type',
    'sender_id',
    'content_encrypted',
])]
class Message extends Model
{
    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }
}