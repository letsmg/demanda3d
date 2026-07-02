<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'client_id',
        'product_id',
        'order_date',
        'delivery_date',
        'price',
        'contracted_description_encrypted',
        'contracted_description_hash',
        'stripe_session_id',
        'amount_total',
        'currency',
        'status',
    ];

    /**
     * Atributos virtuais descriptografados para serialização JSON/Inertia.
     */
    protected $appends = [
        'contracted_description',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'delivery_date' => 'date',
            'price' => 'decimal:2',
            'amount_total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Retorna a descrição contratada descriptografada para exibição no frontend.
     */
    public function getContractedDescriptionAttribute(): ?string
    {
        return EncryptionService::decrypt($this->contracted_description_encrypted);
    }
}