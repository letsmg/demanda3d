<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'client_id',
    'order_id',
    'rating',
    'comment_encrypted',
])]
class Review extends Model
{
    /**
     * Atributos virtuais descriptografados para serialização JSON/Inertia.
     */
    protected $appends = [
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            // IMPORTANTE: NÃO usar cast 'encrypted' — dupla descriptografia com accessors.
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Retorna o comentário descriptografado para exibição no frontend.
     */
    public function getCommentAttribute(): ?string
    {
        return EncryptionService::decrypt($this->comment_encrypted);
    }
}