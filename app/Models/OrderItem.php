<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'snapshot_product_name',
        'snapshot_product_price',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_product_price' => 'decimal:2',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Produto original (pode ser null se o produto foi soft-deletado).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    // ── Helpers ──────────────────────────────────────────────

    /**
     * Retorna o subtotal deste item (preço × quantidade).
     */
    public function subtotal(): float
    {
        return (float) $this->snapshot_product_price * $this->quantity;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.