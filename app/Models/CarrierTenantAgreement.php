<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarrierTenantAgreement extends Model
{
    protected $table = 'carrier_tenant_agreements';

    protected $fillable = ['tenant_id', 'carrier_id', 'status'];

    /**
     * Status possíveis para o acordo comercial.
     */
    public const STATUS_PENDING_TENANT = 'pending_tenant';
    public const STATUS_PENDING_CARRIER = 'pending_carrier';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_REJECTED = 'rejected';

    // ── Relacionamentos ──────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_TENANT,
            self::STATUS_PENDING_CARRIER,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Helpers ──────────────────────────────────────────────

    public function activate(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_TENANT,
            self::STATUS_PENDING_CARRIER,
        ], true);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.