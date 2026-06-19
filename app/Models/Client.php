<?php

namespace App\Models;

use App\Scopes\TenantScope;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'tenant_id',
    'first_name',
    'last_name',
    'display_name',
    'name',
    'doc',
    'doc_encrypted',
    'doc_hash',
    'address',
    'number',
    'state',
    'zipcode',
    'city',
    'phone1',
    'phone1_encrypted',
    'phone1_hash',
    'phone2',
    'phone2_encrypted',
    'phone2_hash',
    'contact1',
    'contact2',
])]
class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'phone1' => 'string',
            'phone2' => 'string',
            'contact1' => 'string',
            'contact2' => 'string',
            'doc_encrypted' => 'encrypted',
            'phone1_encrypted' => 'encrypted',
            'phone2_encrypted' => 'encrypted',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the display name for the client.
     * Uses display_name if set, otherwise combines first_name + last_name, falls back to name.
     */
    public function getDisplayName(): string
    {
        if ($this->display_name) {
            return $this->display_name;
        }

        if ($this->first_name && $this->last_name) {
            return trim($this->first_name . ' ' . $this->last_name);
        }

        return $this->name ?? 'Cliente sem nome';
    }

    /**
     * Get the decrypted document.
     */
    public function getDecryptedDoc(): ?string
    {
        return EncryptionService::decrypt($this->doc_encrypted);
    }

    /**
     * Get the decrypted phone1.
     */
    public function getDecryptedPhone1(): ?string
    {
        return EncryptionService::decrypt($this->phone1_encrypted);
    }

    /**
     * Get the decrypted phone2.
     */
    public function getDecryptedPhone2(): ?string
    {
        return EncryptionService::decrypt($this->phone2_encrypted);
    }

    /**
     * Scope to search by document hash.
     */
    public function scopeByDocHash($query, string $docHash)
    {
        return $query->where('doc_hash', $docHash);
    }

    /**
     * Scope to search by phone hash.
     */
    public function scopeByPhoneHash($query, string $phoneHash, string $column = 'phone1_hash')
    {
        return $query->where($column, $phoneHash);
    }
}