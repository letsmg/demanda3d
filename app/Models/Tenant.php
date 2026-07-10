<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name_encrypted',
        'company_name_hash',
        'fantasy_name',
        'fantasy_slug',
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
        'active',
        'rating_average',
        'rating_count',
    ];

    /**
     * Apenas company_name (razão social) permanece criptografado por LGPD.
     * Todos os outros campos são públicos — dados da empresa visíveis na loja.
     *
     * ATENÇÃO: Não informe dados pessoais nos campos da empresa.
     * Nome fantasia, CNPJ e endereço comercial são públicos por lei.
     */
    protected $appends = [
        'company_name',
        'logo_url',
        'banner_url',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'rating_average' => 'decimal:2',
            'rating_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if (empty($tenant->fantasy_slug) && !empty($tenant->fantasy_name)) {
                $tenant->fantasy_slug = static::generateUniqueFantasySlug($tenant->fantasy_name);
            }
        });

        static::updating(function (Tenant $tenant) {
            if ($tenant->isDirty('fantasy_name') && !$tenant->isDirty('fantasy_slug')) {
                $tenant->fantasy_slug = static::generateUniqueFantasySlug($tenant->fantasy_name, $tenant->id);
            }
        });
    }

    public static function generateUniqueFantasySlug(string $fantasyName, ?int $excludeId = null): string
    {
        $baseSlug = \Illuminate\Support\Str::slug($fantasyName);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = static::where('fantasy_slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getCompanyNameAttribute(): ?string
    {
        return \App\Services\EncryptionService::decrypt($this->company_name_encrypted);
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
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
