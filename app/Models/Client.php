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
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'tenant_id',
    'email',
    'password',
    'display_name',
    'doc_type',
    'first_name_encrypted',
    'first_name_hash',
    'last_name_encrypted',
    'last_name_hash',
    'doc_encrypted',
    'doc_hash',
    'address_encrypted',
    'address_hash',
    'number_encrypted',
    'number_hash',
    'state_encrypted',
    'state_hash',
    'zipcode_encrypted',
    'zipcode_hash',
    'city_encrypted',
    'city_hash',
    'phone1_encrypted',
    'phone1_hash',
    'phone2_encrypted',
    'phone2_hash',
    'contact1_encrypted',
    'contact1_hash',
    'contact2_encrypted',
    'contact2_hash',
])]
class Client extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * Atributos virtuais que DEVEM ser serializados para JSON/Inertia.
     * Sem o $appends, os accessors get{Nome}Attribute NÃO são incluídos
     * no toArray() e os dados ficam nulos no frontend Vue.
     */
    protected $appends = [
        'first_name',
        'last_name',
        'doc',
        'address',
        'number',
        'state',
        'zipcode',
        'city',
        'phone1',
        'phone2',
        'contact1',
        'contact2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Casts removidos intencionalmente.
     *
     * Os campos *_encrypted são tratados manualmente pelos accessors
     * via EncryptionService::decrypt(). Os casts nativos 'encrypted' do
     * Laravel causariam dupla descriptografia, corrompendo os dados.
     *
     * Os campos 'phone1', 'phone2', 'contact1', 'contact2' são VIRTUAIS
     * (não existem como colunas no banco) — são populados pelos accessors.
     * Mantê-los nos casts faria o Laravel tentar buscar colunas inexistentes.
     */
    protected function casts(): array
    {
        return [];
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
     * Accessors para descriptografar campos sensíveis ao serializar para JSON/Inertia.
     * O Laravel inclui automaticamente accessors no toArray() quando usam get{Nome}Attribute.
     */
    public function getFirstNameAttribute(): ?string
    {
        return EncryptionService::decrypt($this->first_name_encrypted);
    }

    public function getLastNameAttribute(): ?string
    {
        return EncryptionService::decrypt($this->last_name_encrypted);
    }

    public function getDocAttribute(): ?string
    {
        return EncryptionService::decrypt($this->doc_encrypted);
    }

    public function getAddressAttribute(): ?string
    {
        return EncryptionService::decrypt($this->address_encrypted);
    }

    public function getNumberAttribute(): ?string
    {
        return EncryptionService::decrypt($this->number_encrypted);
    }

    public function getStateAttribute(): ?string
    {
        return EncryptionService::decrypt($this->state_encrypted);
    }

    public function getZipcodeAttribute(): ?string
    {
        return EncryptionService::decrypt($this->zipcode_encrypted);
    }

    public function getCityAttribute(): ?string
    {
        return EncryptionService::decrypt($this->city_encrypted);
    }

    public function getPhone1Attribute(): ?string
    {
        return EncryptionService::decrypt($this->phone1_encrypted);
    }

    public function getPhone2Attribute(): ?string
    {
        return EncryptionService::decrypt($this->phone2_encrypted);
    }

    public function getContact1Attribute(): ?string
    {
        return EncryptionService::decrypt($this->contact1_encrypted);
    }

    public function getContact2Attribute(): ?string
    {
        return EncryptionService::decrypt($this->contact2_encrypted);
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