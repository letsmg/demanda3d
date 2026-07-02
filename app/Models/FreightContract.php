<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'tenant_id',
    'carrier_id',
    'order_id',
    'pickup_location',
    'delivery_location',
    'cargo_description',
    'pickup_date',
    'estimated_delivery_date',
    'delivered_date',
    'freight_paid',
    'freight_value',
    'status',
    'notes',
])]
class FreightContract extends Model
{
    protected $table = 'freight_contracts';

    protected function casts(): array
    {
        return [
            'pickup_date' => 'date',
            'estimated_delivery_date' => 'date',
            'delivered_date' => 'date',
            'freight_paid' => 'boolean',
            'freight_value' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}