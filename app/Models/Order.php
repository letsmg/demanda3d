<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'client_id',
    'order_date',
    'delivery_date',
    'price',
    'contracted_description',
])]
class Order extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'client_id' => 'integer',
            'order_date' => 'date',
            'delivery_date' => 'date',
            'price' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
