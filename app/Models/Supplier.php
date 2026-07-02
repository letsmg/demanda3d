<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'name',
    'document_hash',
    'document_encrypted',
    'contact_encrypted',
])]
class Supplier extends Model
{
    /**
     * Atributos virtuais que DEVEM ser serializados para JSON/Inertia.
     * 'document' e 'contact' são descriptografados pelos accessors.
     */
    protected $appends = [
        'document',
        'contact',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(Input::class);
    }

    /**
     * Retorna o documento descriptografado para exibição no frontend (Vue/Inertia).
     * O Laravel serializa automaticamente os accessors no toArray().
     */
    public function getDocumentAttribute(): ?string
    {
        return EncryptionService::decrypt($this->document_encrypted);
    }

    /**
     * Retorna o contato descriptografado para exibição no frontend.
     */
    public function getContactAttribute(): ?string
    {
        return EncryptionService::decrypt($this->contact_encrypted);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
