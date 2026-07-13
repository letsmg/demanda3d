<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'name', 'data_nascimento', 'doc_type', 'ie',
    'document_encrypted', 'document_hash',
    'address_encrypted', 'address_hash',
    'number_encrypted', 'number_hash',
    'district_encrypted', 'district_hash',
    'city_encrypted', 'city_hash',
    'state', 'zipcode',
    'contact1_encrypted', 'contact1_hash',
    'phone1_encrypted', 'phone1_hash',
    'contact2_encrypted', 'contact2_hash',
    'phone2_encrypted', 'phone2_hash',
    'website', 'notes', 'is_active',
    'is_blocked', 'blocked_at', 'blocked_reason',
    'state_id',
])]
class Carrier extends Model
{
    use HasFactory;

    protected $appends = [
        'document', 'address', 'number', 'district', 'city',
        'contact1', 'phone1', 'contact2', 'phone2',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'is_blocked' => 'boolean',
            'data_nascimento' => 'date',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Relacionamento 1:1 com a tabela users (autenticação unificada).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Estados de atuação (legado — será substituído por coverage_ranges).
     */
    public function states(): BelongsToMany
    {
        return $this->belongsToMany(State::class, 'carrier_state')
            ->withTimestamps();
    }

    /**
     * Faixas de CEP de cobertura de entrega.
     */
    public function coverageRanges(): HasMany
    {
        return $this->hasMany(CarrierCoverageRange::class);
    }

    /**
     * Acordos comerciais com tenants (vendedores).
     */
    public function tenantAgreements(): HasMany
    {
        return $this->hasMany(CarrierTenantAgreement::class);
    }

    /**
     * Contratos de frete vinculados a esta transportadora.
     */
    public function freightContracts(): HasMany
    {
        return $this->hasMany(FreightContract::class);
    }

    /**
     * Tenants com acordo ativo (via pivot carrier_tenant_agreements).
     */
    public function activeTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'carrier_tenant_agreements')
            ->wherePivot('status', 'active')
            ->withTimestamps();
    }

    // ── Scopes ───────────────────────────────────────────────

    /**
     * Scope para transportadoras ativas e não bloqueadas.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('is_blocked', false);
    }

    /**
     * Scope para verificar se um determinado CEP está dentro de
     * alguma faixa de cobertura desta transportadora.
     */
    public function scopeCoversCep($query, string $cep)
    {
        return $query->whereHas('coverageRanges', function ($q) use ($cep) {
            $q->where('cep_start', '<=', $cep)
              ->where('cep_end', '>=', $cep);
        });
    }

    // ── Accessors ────────────────────────────────────────────

    public function getDocumentAttribute(): ?string { return EncryptionService::decrypt($this->document_encrypted); }
    public function getAddressAttribute(): ?string { return EncryptionService::decrypt($this->address_encrypted); }
    public function getNumberAttribute(): ?string { return EncryptionService::decrypt($this->number_encrypted); }
    public function getDistrictAttribute(): ?string { return EncryptionService::decrypt($this->district_encrypted); }
    public function getCityAttribute(): ?string { return EncryptionService::decrypt($this->city_encrypted); }
    public function getContact1Attribute(): ?string { return EncryptionService::decrypt($this->contact1_encrypted); }
    public function getPhone1Attribute(): ?string { return EncryptionService::decrypt($this->phone1_encrypted); }
    public function getContact2Attribute(): ?string { return EncryptionService::decrypt($this->contact2_encrypted); }
    public function getPhone2Attribute(): ?string { return EncryptionService::decrypt($this->phone2_encrypted); }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Retorna o e-mail do transportador via relacionamento com users.
     */
    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Verifica se o transportador tem acordo ativo com um determinado tenant.
     */
    public function hasActiveAgreementWith(int $tenantId): bool
    {
        return $this->tenantAgreements()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Verifica se o transportador cobre um determinado CEP.
     */
    public function coversCep(string $cep): bool
    {
        return $this->coverageRanges()
            ->where('cep_start', '<=', $cep)
            ->where('cep_end', '>=', $cep)
            ->exists();
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.