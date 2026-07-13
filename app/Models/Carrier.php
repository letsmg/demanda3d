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
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'company_name_encrypted', 'company_name_hash',
    'fantasy_name', 'slug', 'document_type',
    'document_encrypted', 'document_hash',
    'address_encrypted',
    'phone_encrypted',
    'logo_path', 'website_url',
    'rating_average', 'rating_count',
    'is_active',
])]
class Carrier extends Model
{
    use HasFactory;

    protected $appends = [
        'company_name', 'document', 'address', 'phone',
    ];

    protected function casts(): array
    {
        return [
            'is_active'       => 'boolean',
            'rating_average'  => 'decimal:2',
            'rating_count'    => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Carrier $carrier) {
            if (empty($carrier->slug) && ! empty($carrier->fantasy_name)) {
                $carrier->slug = static::generateUniqueSlug($carrier->fantasy_name);
            }
        });

        static::updating(function (Carrier $carrier) {
            if ($carrier->isDirty('fantasy_name') && ! $carrier->isDirty('slug')) {
                $carrier->slug = static::generateUniqueSlug($carrier->fantasy_name, $carrier->id);
            }
        });
    }

    // ── Relacionamentos ──────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverageRanges(): HasMany
    {
        return $this->hasMany(CarrierCoverageRange::class);
    }

    public function tenantAgreements(): HasMany
    {
        return $this->hasMany(CarrierTenantAgreement::class);
    }

    public function freightContracts(): HasMany
    {
        return $this->hasMany(FreightContract::class);
    }

    public function activeTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'carrier_tenant_agreements')
            ->wherePivot('status', 'active')
            ->withTimestamps();
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCoversCep($query, string $cep)
    {
        return $query->whereHas('coverageRanges', function ($q) use ($cep) {
            $q->where('cep_start', '<=', $cep)
              ->where('cep_end', '>=', $cep);
        });
    }

    // ── Accessors ────────────────────────────────────────────

    public function getCompanyNameAttribute(): ?string { return EncryptionService::decrypt($this->company_name_encrypted); }
    public function getDocumentAttribute(): ?string    { return EncryptionService::decrypt($this->document_encrypted); }
    public function getAddressAttribute(): ?string     { return EncryptionService::decrypt($this->address_encrypted); }
    public function getPhoneAttribute(): ?string       { return EncryptionService::decrypt($this->phone_encrypted); }

    // ── Helpers ──────────────────────────────────────────────

    public function getEmailAttribute(): ?string
    {
        return $this->user?->email;
    }

    public function hasActiveAgreementWith(int $tenantId): bool
    {
        return $this->tenantAgreements()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->exists();
    }

    public function coversCep(string $cep): bool
    {
        return $this->coverageRanges()
            ->where('cep_start', '<=', $cep)
            ->where('cep_end', '>=', $cep)
            ->exists();
    }

    public static function generateUniqueSlug(string $fantasyName, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($fantasyName);
        $slug     = $baseSlug;
        $counter  = 1;

        while (true) {
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.