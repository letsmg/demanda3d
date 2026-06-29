<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'tenant_id',
    'name',
    'slug',
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
    'meta_title',
    'meta_description',
    'canonical_url',
    'og_image',
    'approximate_cost',
    'sale_price',
    'is_active',
    'moderation_status',
])]
class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Product $product) {
            if (empty($product->slug) && !empty($product->name)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->tenant_id);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->tenant_id, $product->id);
            }
        });
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

    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria::class, 'categoria_produto', 'produto_id', 'categoria_id')
            ->withTimestamps();
    }

    /**
     * Verifica se o produto pertence a pelo menos uma categoria de conteúdo adulto.
     */
    public function hasAdultContent(): bool
    {
        return $this->categorias()->whereAdultContent()->exists();
    }

    /**
     * Scope para filtrar produtos que contenham categorias adultas.
     */
    public function scopeWhereHasAdultCategories($query)
    {
        return $query->whereHas('categorias', function ($q) {
            $q->whereAdultContent();
        });
    }

    /**
     * Scope para excluir produtos de categorias adultas da listagem.
     */
    public function scopeWithoutAdultCategories($query)
    {
        return $query->whereDoesntHave('categorias', function ($q) {
            $q->whereAdultContent();
        });
    }

    /**
     * Gera um slug único para o produto dentro do tenant.
     */
    public static function generateUniqueSlug(string $name, ?int $tenantId = null, ?int $excludeId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (true) {
            $query = static::withoutGlobalScopes()->where('slug', $slug);
            if ($tenantId) {
                $query->where('tenant_id', $tenantId);
            }
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
