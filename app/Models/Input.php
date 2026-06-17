<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'filaments',
    'energy',
    'dt_buy',
    'cost_buy',
    'purge',
])]
class Input extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'energy' => 'decimal:2',
            'dt_buy' => 'date',
            'cost_buy' => 'decimal:2',
            'purge' => 'decimal:2',
        ];
    }
}