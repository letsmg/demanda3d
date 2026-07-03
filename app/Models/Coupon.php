<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'tenant_id', 'category_id', 'code', 'type', 'value',
        'min_order_value', 'max_uses', 'used_count',
        'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'          => 'decimal:2',
            'min_order_value' => 'decimal:2',
            'max_uses'       => 'integer',
            'used_count'     => 'integer',
            'starts_at'      => 'datetime',
            'expires_at'     => 'datetime',
            'is_active'      => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /**
     * Aplica o desconto a um valor total.
     */
    public function applyTo(float $total): float
    {
        if ($this->type === 'percentage') {
            return $total * (1 - $this->value / 100);
        }

        return max(0, $total - (float) $this->value);
    }

    /**
     * Verifica se o cupom é válido para uso.
     */
    public function isValid(float $orderTotal, ?int $tenantId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        if ($this->min_order_value !== null && $orderTotal < (float) $this->min_order_value) {
            return false;
        }

        // Cupom de tenant específico só vale para aquele tenant
        if ($this->tenant_id !== null && $tenantId !== null && $this->tenant_id !== $tenantId) {
            return false;
        }

        return true;
    }
}