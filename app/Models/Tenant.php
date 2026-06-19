<?php

namespace App\Models;

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
        'company_name',
        'fantasy_name',
        'document',
        'document_encrypted',
        'document_hash',
        'phone',
        'phone_encrypted',
        'phone_hash',
        'address',
        'number',
        'district',
        'city',
        'state',
        'zipcode',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'document_encrypted' => 'encrypted',
            'phone_encrypted' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    /**
     * Get the decrypted document.
     */
    public function getDecryptedDocument(): ?string
    {
        return EncryptionService::decrypt($this->document_encrypted);
    }

    /**
     * Get the decrypted phone.
     */
    public function getDecryptedPhone(): ?string
    {
        return EncryptionService::decrypt($this->phone_encrypted);
    }
}