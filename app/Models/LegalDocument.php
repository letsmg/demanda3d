<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LegalDocument extends Model
{
    protected $fillable = [
        'type',
        'version',
        'title',
        'content_html',
        'published_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Tipos de documentos legais suportados.
     */
    public const TYPE_TERMS_OF_SERVICE = 'terms_of_service';
    public const TYPE_PRIVACY_POLICY = 'privacy_policy';

    /**
     * Consentimentos registrados para este documento.
     */
    public function consents(): HasMany
    {
        return $this->hasMany(VisitorLegalConsent::class);
    }

    /**
     * Scope para filtrar por tipo de documento.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para buscar apenas documentos publicados (não arquivados).
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->whereNull('archived_at');
    }

    /**
     * Retorna a versão ativa mais recente de um tipo de documento.
     */
    public static function getActive(string $type): ?self
    {
        return static::ofType($type)
            ->published()
            ->orderByDesc('version')
            ->first();
    }
}