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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'rating_average' => 'decimal:2',
            'rating_count' => 'integer',
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