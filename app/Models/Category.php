<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'slug', 'is_adult'];

    protected function casts(): array
    {
        return ['is_adult' => 'boolean'];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id')
            ->withTimestamps();
    }

    public function scopeWhereAdultContent($query, bool $isAdult = true)
    {
        return $query->where('is_adult', $isAdult);
    }
}