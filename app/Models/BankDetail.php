<?php

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'carrier_id',
        'bank_name',
        'routing_number_encrypted',
        'account_number_encrypted',
        'bank_pix_key_encrypted',
        'account_holder_name',
        'account_holder_doc_encrypted',
        'account_holder_doc_hash',
        'consented',
        'consented_at',
        'consent_ip',
        'consent_term_version',
        'pending_token',
        'pending_data',
        'pending_at',
    ];

    protected $appends = [
        'routing_number',
        'account_number',
        'bank_pix_key',
        'account_holder_doc',
    ];

    protected $casts = [
        'consented'    => 'boolean',
        'consented_at'  => 'datetime',
        'pending_at'    => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    // Accessors — descriptografam em memória
    public function getRoutingNumberAttribute(): ?string
    {
        return EncryptionService::decrypt($this->routing_number_encrypted);
    }

    public function getAccountNumberAttribute(): ?string
    {
        return EncryptionService::decrypt($this->account_number_encrypted);
    }

    public function getBankPixKeyAttribute(): ?string
    {
        return EncryptionService::decrypt($this->bank_pix_key_encrypted);
    }

    public function getAccountHolderDocAttribute(): ?string
    {
        return EncryptionService::decrypt($this->account_holder_doc_encrypted);
    }
}