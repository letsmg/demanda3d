<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'tenant_id',
    'name',
    'description',
    'height',
    'width',
    'approximate_weight',
    'waste_weight',
    'material_type',
    'print_time',
    'pieces_produced',
    'maintenance_fee',
    'painting_time',
    'painting_material',
    'painting_cost',
    'extras_cost',
    'approximate_cost',
    'sale_price',
    'is_active',
])]
class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'maintenance_fee' => 'decimal:2',
            'painting_cost' => 'decimal:2',
            'extras_cost' => 'decimal:2',
            'approximate_cost' => 'decimal:2',
            'sale_price' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function firstImage(): ?ProductImage
    {
        return $this->images()->first();
    }
}