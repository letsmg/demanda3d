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
use Illuminate\Support\Carbon;

#[Fillable([
    'email',
    'display_name',
    'first_name_encrypted',
    'first_name_hash',
    'last_name_encrypted',
    'last_name_hash',
    'password',
    'access_level',
    'data_nascimento',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Atributos virtuais descriptografados para serialização JSON/Inertia.
     */
    protected $appends = [
        'first_name',
        'last_name',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'access_level' => UserAccessLevel::class,
            'data_nascimento' => 'date',
        ];
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function vendorCarriers(): HasMany
    {
        return $this->hasMany(VendorCarrier::class);
    }

    public function isAdmin(): bool
    {
        return $this->access_level === UserAccessLevel::ADMIN;
    }

    public function isManagement(): bool
    {
        return $this->access_level === UserAccessLevel::MANAGEMENT;
    }

    public function isOperational(): bool
    {
        return $this->access_level === UserAccessLevel::OPERATIONAL;
    }

    public function isStaff(): bool
    {
        return $this->access_level->isStaff();
    }

    public function isCustomer(): bool
    {
        return $this->access_level === UserAccessLevel::CUSTOMER;
    }

    public function canAccessFinancials(): bool
    {
        return $this->access_level->canAccessFinancials();
    }

    /**
     * Retorna a idade do usuário baseada na data_nascimento.
     * Retorna null se data_nascimento não estiver definida.
     */
    public function getAge(): ?int
    {
        if (!$this->data_nascimento) {
            return null;
        }

        return (int) $this->data_nascimento->diffInYears(now());
    }

    /**
     * Verifica se o usuário tem 18 anos ou mais.
     * Usuários sem data_nascimento definida são tratados como menores.
     */
    public function is18Plus(): bool
    {
        $age = $this->getAge();

        return $age !== null && $age >= 18;
    }

    /**
     * Determina se o usuário pode visualizar conteúdo adulto.
     * Staff (Admin, Management, Operational) sempre pode.
     * Customers precisam ter 18+ anos.
     */
    public function canAccessAdultContent(): bool
    {
        if ($this->isStaff()) {
            return true;
        }

        return $this->is18Plus();
    }

    public function getDisplayName(): string
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        $firstName = $this->getDecryptedFirstName();
        $lastName = $this->getDecryptedLastName();

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

    // ── Accessors para serialização automática ──────────────

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
