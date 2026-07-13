<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'order_date',
        'delivery_date',
        'stripe_session_id',
        'amount_total',
        'currency',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'order_date'    => 'date',
            'delivery_date' => 'date',
            'amount_total'  => 'decimal:2',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Itens do pedido (snapshots imutáveis).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Etiquetas de envio vinculadas a este pedido.
     */
    public function labels(): HasMany
    {
        return $this->hasMany(OrderLabel::class);
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Calcula o total do pedido somando os snapshots dos itens.
     */
    public function calculateTotal(): float
    {
        return (float) $this->items->sum(fn (OrderItem $item) => $item->subtotal());
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.