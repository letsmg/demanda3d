<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use App\Services\EncryptionService;
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
    /**
     * Atributos virtuais descriptografados para serialização JSON/Inertia.
     */
    protected $appends = [
        'content',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    /**
     * Retorna o conteúdo descriptografado para exibição no frontend.
     */
    public function getContentAttribute(): ?string
    {
        return EncryptionService::decrypt($this->content_encrypted);
    }
}