<?php

namespace App\Models;

use App\Enums\UserAccessLevel;
use App\Services\EncryptionService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'access_level' => UserAccessLevel::class,
        ];
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }

    public function isAdmin(): bool
    {
        return $this->access_level === UserAccessLevel::ADMIN;
    }

    public function isPartner(): bool
    {
        return $this->access_level === UserAccessLevel::PARTNER;
    }

    public function isCustomer(): bool
    {
        return $this->access_level === UserAccessLevel::CUSTOMER;
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

    public function scopeByFirstNameHash($query, string $hash)
    {
        return $query->where('first_name_hash', $hash);
    }

    public function scopeByLastNameHash($query, string $hash)
    {
        return $query->where('last_name_hash', $hash);
    }

}