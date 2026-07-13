<?php

namespace App\Models;

use App\Enums\UserAccessLevel;
use App\Services\EncryptionService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'email',
    'display_name',
    'first_name_encrypted',
    'first_name_hash',
    'last_name_encrypted',
    'last_name_hash',
    'password',
    'access_level',
    'is_active',
    'birth_date',
    'email_verified_at',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $appends = [
        'first_name',
        'last_name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'access_level'      => UserAccessLevel::class,
            'birth_date'        => 'date',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function carrier(): HasOne
    {
        return $this->hasOne(Carrier::class);
    }

    public function vendorCarriers(): HasMany
    {
        return $this->hasMany(VendorCarrier::class);
    }

    // ── Verificações ─────────────────────────────────────────

    public function isAdmin(): bool    { return $this->access_level === UserAccessLevel::ADMIN; }
    public function isSeller(): bool   { return $this->access_level->isSeller(); }
    public function isSeller1(): bool  { return $this->access_level === UserAccessLevel::SELLER_1; }
    public function isSeller2(): bool  { return $this->access_level === UserAccessLevel::SELLER_2; }
    public function isCarrier(): bool  { return $this->access_level->isCarrier(); }
    public function isCustomer(): bool { return $this->access_level === UserAccessLevel::CUSTOMER; }

    public function isStaff(): bool
    {
        return $this->access_level->isSeller() || $this->access_level->isAdmin();
    }

    public function canAccessFinancials(): bool
    {
        return $this->access_level->canAccessFinancials();
    }

    public function getAge(): ?int
    {
        if (! $this->birth_date) {
            return null;
        }
        return (int) $this->birth_date->diffInYears(now());
    }

    public function is18Plus(): bool
    {
        $age = $this->getAge();
        return $age !== null && $age >= 18;
    }

    public function canAccessAdultContent(): bool
    {
        if ($this->isStaff()) {
            return true;
        }
        return $this->is18Plus();
    }

    // ── Display name ─────────────────────────────────────────

    public function getDisplayName(): string
    {
        if ($this->display_name) {
            return $this->display_name;
        }
        $firstName = $this->getDecryptedFirstName();
        $lastName  = $this->getDecryptedLastName();
        if ($firstName && $lastName) {
            return trim($firstName . ' ' . $lastName);
        }
        return 'Usuário';
    }

    public function getDecryptedFirstName(): ?string
    {
        return EncryptionService::decrypt($this->first_name_encrypted);
    }

    public function getDecryptedLastName(): ?string
    {
        return EncryptionService::decrypt($this->last_name_encrypted);
    }

    public function getFirstNameAttribute(): ?string
    {
        return $this->getDecryptedFirstName();
    }

    public function getLastNameAttribute(): ?string
    {
        return $this->getDecryptedLastName();
    }

    public function scopeByFirstNameHash($query, string $hash)
    {
        return $query->where('first_name_hash', $hash);
    }

    public function scopeByLastNameHash($query, string $hash)
    {
        return $query->where('last_name_hash', $hash);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.