<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nome',
        'slug',
        'maior_de_idade',
    ];

    protected function casts(): array
    {
        return [
            'maior_de_idade' => 'boolean',
        ];
    }

    public function produtos(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'categoria_produto', 'categoria_id', 'produto_id')
            ->withTimestamps();
    }

    public function scopeWhereAdultContent($query, bool $isAdult = true)
    {
        return $query->where('maior_de_idade', $isAdult);
    }
}