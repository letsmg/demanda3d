<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

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
    'doc_type',
    'ie',
    // Documento (paridade)
    'document_hash',
    'document_encrypted',
    // Contato original
    'contact_encrypted',
    // Endereço (paridade)
    'address_encrypted',
    'address_hash',
    'number_encrypted',
    'number_hash',
    'district_encrypted',
    'district_hash',
    'city_encrypted',
    'city_hash',
    // Texto puro
    'state',
    'zipcode',
    // Contato 1
    'contact1_encrypted',
    'contact1_hash',
    'phone1_encrypted',
    'phone1_hash',
    // Contato 2
    'contact2_encrypted',
    'contact2_hash',
    'phone2_encrypted',
    'phone2_hash',
    // Dados públicos
    'email',
    'website',
    'notes',
    'is_active',
])]
class Supplier extends Model
{
    protected $appends = [
        'document',
        'contact',
        'address',
        'number',
        'district',
        'city',
        'contact1',
        'phone1',
        'contact2',
        'phone2',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

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

    public function accountsPayable(): HasMany
    {
        return $this->hasMany(AccountPayable::class);
    }

    // ── Accessors de descriptografia ──────────────────────

    public function getDocumentAttribute(): ?string
    {
        return EncryptionService::decrypt($this->document_encrypted);
    }

    public function getContactAttribute(): ?string
    {
        return EncryptionService::decrypt($this->contact_encrypted);
    }

    public function getAddressAttribute(): ?string
    {
        return EncryptionService::decrypt($this->address_encrypted);
    }

    public function getNumberAttribute(): ?string
    {
        return EncryptionService::decrypt($this->number_encrypted);
    }

    public function getDistrictAttribute(): ?string
    {
        return EncryptionService::decrypt($this->district_encrypted);
    }

    public function getCityAttribute(): ?string
    {
        return EncryptionService::decrypt($this->city_encrypted);
    }

    public function getContact1Attribute(): ?string
    {
        return EncryptionService::decrypt($this->contact1_encrypted);
    }

    public function getPhone1Attribute(): ?string
    {
        return EncryptionService::decrypt($this->phone1_encrypted);
    }

    public function getContact2Attribute(): ?string
    {
        return EncryptionService::decrypt($this->contact2_encrypted);
    }

    public function getPhone2Attribute(): ?string
    {
        return EncryptionService::decrypt($this->phone2_encrypted);
    }
}
