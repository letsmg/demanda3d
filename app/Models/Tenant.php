<?php

namespace App\Models;

use App\Models\Review;
use App\Services\EncryptionService;
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
        'fantasy_name_encrypted',
        'fantasy_name_hash',
        'document_encrypted',
        'document_hash',
        'phone_encrypted',
        'phone_hash',
        'address_encrypted',
        'address_hash',
        'number_encrypted',
        'number_hash',
        'district_encrypted',
        'district_hash',
        'city_encrypted',
        'city_hash',
        'state',
        'zipcode',
        'active',
        'rating_average',
        'rating_count',
    ];

    /**
     * Atributos virtuais descriptografados para serialização JSON/Inertia.
     */
    protected $appends = [
        'company_name',
        'fantasy_name',
        'document',
        'phone',
        'address',
        'number',
        'district',
        'city',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'rating_average' => 'decimal:2',
            'rating_count' => 'integer',
            // IMPORTANTE: NÃO usar cast 'encrypted' — isso causaria dupla descriptografia
            // com os accessors manuais que usam EncryptionService::decrypt().
        ];
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

    // ── Accessors de descriptografia ────────────────────────────

    public function getCompanyNameAttribute(): ?string
    {
        return EncryptionService::decrypt($this->company_name_encrypted);
    }

    public function getFantasyNameAttribute(): ?string
    {
        return EncryptionService::decrypt($this->fantasy_name_encrypted);
    }

    public function getDocumentAttribute(): ?string
    {
        return EncryptionService::decrypt($this->document_encrypted);
    }

    public function getPhoneAttribute(): ?string
    {
        return EncryptionService::decrypt($this->phone_encrypted);
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
}

// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.