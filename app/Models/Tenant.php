<?php

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name_encrypted',
        'company_name_hash',
        'fantasy_name',
        'fantasy_slug',
        'document_type',
        'document',
        'phone',
        'address',
        'number',
        'district',
        'city',
        'state',
        'zipcode',
        'logo_path',
        'banner_path',
        'legal_responsible_name',
        'active',
        'rating_average',
        'rating_count',
    ];

    protected $appends = [
        'company_name',
        'logo_url',
        'banner_url',
    ];

    protected function casts(): array
    {
        return [
            'active'         => 'boolean',
            'rating_average' => 'decimal:2',
            'rating_count'   => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->fantasy_slug)) {
                $source = ! empty($tenant->fantasy_name)
                    ? $tenant->fantasy_name
                    : 'tenant-' . uniqid();

                $tenant->fantasy_slug = static::generateUniqueFantasySlug($source);
            }
        });

        static::updating(function (Tenant $tenant) {
            if ($tenant->isDirty('fantasy_name') && ! $tenant->isDirty('fantasy_slug')) {
                $tenant->fantasy_slug = static::generateUniqueFantasySlug(
                    ! empty($tenant->fantasy_name) ? $tenant->fantasy_name : 'tenant-' . uniqid(),
                    $tenant->id
                );
            }
        });
    }

    public static function generateUniqueFantasySlug(string $fantasyName, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($fantasyName);
        $slug     = $baseSlug;
        $counter  = 1;

        while (true) {
            $query = static::where('fantasy_slug', $slug);
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

    // ── Accessors ────────────────────────────────────────────

    public function getCompanyNameAttribute(): ?string
    {
        return EncryptionService::decrypt($this->company_name_encrypted);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (empty($this->logo_path)) {
            return null;
        }
        return url('storage/' . $this->logo_path);
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (empty($this->banner_path)) {
            return null;
        }
        return url('storage/' . $this->banner_path);
    }

    // ── Relacionamentos ──────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function inputs(): HasMany
    {
        return $this->hasMany(Input::class);
    }

    public function carrierTenantAgreements(): HasMany
    {
        return $this->hasMany(CarrierTenantAgreement::class);
    }

    public function activeCarriers(): BelongsToMany
    {
        return $this->belongsToMany(Carrier::class, 'carrier_tenant_agreements')
            ->wherePivot('status', 'active')
            ->withTimestamps();
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
