<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserAccessLevel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $display_name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property UserAccessLevel $access_level
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['first_name', 'last_name', 'display_name', 'email', 'password', 'access_level'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
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

    /**
     * Get the display name for the user.
     * Uses display_name if set, otherwise combines first_name + last_name.
     */
    public function getDisplayName(): string
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        return trim($this->first_name . ' ' . $this->last_name);
    }
}