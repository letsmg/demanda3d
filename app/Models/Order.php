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
        'delivered_at',
        'stripe_session_id',
        'amount_total',
        'currency',
        'status',
        'snapshot_address',
        'snapshot_product_name',
        'snapshot_product_price',
    ];

    protected function casts(): array
    {
        return [
            'order_date'            => 'date',
            'delivery_date'         => 'date',
            'delivered_at'          => 'datetime',
            'amount_total'          => 'decimal:2',
            'snapshot_product_price'=> 'decimal:2',
        ];
    }

    /**
     * Verifica se o pedido pode ser cancelado pelo cliente (CDC: 7 dias após entrega).
     */
    public function canBeCancelled(): bool
    {
        // Pedidos não pagos podem ser cancelados
        if (! in_array($this->status, ['paid', 'processing', 'confirmed'])) {
            return false;
        }

        // Se não foi entregue ainda, pode cancelar
        if ($this->delivered_at === null) {
            return true;
        }

        // CDC: 7 dias corridos após a entrega
        return $this->delivered_at->diffInDays(now()) < 7;
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