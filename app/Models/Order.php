<?php

namespace App\Models;

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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}