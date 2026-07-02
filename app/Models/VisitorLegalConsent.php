<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorLegalConsent extends Model
{
    protected $fillable = [
        'legal_document_id',
        'status',
        'ip_hash',
        'ip_encrypted',
        'user_agent',
        'geolocation',
        'client_id',
        'user_id',
    ];

    /**
     * Status de consentimento suportados.
     */
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';

    /**
     * Documento legal relacionado.
     */
    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class);
    }

    /**
     * Cliente que forneceu o consentimento (se cadastrado).
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Usuário staff que forneceu o consentimento (se cadastrado).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar por status.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope para filtrar recusas.
     */
    public function scopeDeclined($query)
    {
        return $query->where('status', self::STATUS_DECLINED);
    }
}