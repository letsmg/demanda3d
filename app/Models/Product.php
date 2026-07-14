<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    'approximate_cost',
    'sale_price',
    'is_active',
    'moderation_status',
])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Accessors SEO — 100% derivados dos campos nativos do produto.
     *
     * meta_title        → name (máx. 120 chars)
     * meta_description  → description (strip_tags, máx. 320 chars)
     * meta_keywords     → name + categorias + termos de nicho
     * canonical_url     → route('store.detail', slug)
     * og_image          → primeira imagem do produto ou fallback default
     * schema_markup     → JSON-LD Product (via ProductService)
     * google_tag_manager → script GTM + dataLayer (via ProductService)
     */
    protected $appends = [
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_image',
        'schema_markup',
        'google_tag_manager',
    ];

    // ─── Accessors SEO ───────────────────────────────────────────────

    public function getMetaTitleAttribute(): string
    {
        return mb_substr(trim($this->attributes['name'] ?? ''), 0, 120);
    }

    public function getMetaDescriptionAttribute(): string
    {
        $clean = trim(strip_tags($this->attributes['description'] ?? ''));

        return mb_substr($clean ?: ($this->attributes['name'] ?? ''), 0, 320);
    }

    public function getMetaKeywordsAttribute(): string
    {
        $keywords = [];
        $nameWords = explode(' ', strtolower(trim($this->attributes['name'] ?? '')));

        foreach ($nameWords as $word) {
            $clean = preg_replace('/[^a-zà-ú0-9]/', '', $word);
            if (mb_strlen($clean) >= 3 && ! in_array($clean, ['com', 'para', 'que', 'dos', 'das', 'uma', 'são'])) {
                $keywords[] = $clean;
            }
        }

        $keywords[] = strtolower(trim($this->attributes['name'] ?? ''));

        // Categorias
        if ($this->relationLoaded('categories')) {
            foreach ($this->categories as $cat) {
                $name = strtolower($cat->name);
                $keywords[] = $name;
                $keywords[] = $name . ' impressão 3d';
            }
        }

        // Termos nicho
        $keywords[] = 'impressão 3d';
        $keywords[] = 'produto 3d';
        $keywords[] = 'marketplace 3d';
        $keywords[] = 'impressão sob demanda';

        if (! empty($this->attributes['material_type'])) {
            $keywords[] = 'filamento ' . strtolower($this->attributes['material_type']);
        }

        $keywords = array_unique(array_filter($keywords));

        return mb_substr(implode(', ', $keywords), 0, 255);
    }

    public function getCanonicalUrlAttribute(): string
    {
        $slug = $this->attributes['slug'] ?? '';

        if (empty($slug)) {
            return '';
        }

        return route('store.detail', ['slug' => $slug]);
    }

    public function getOgImageAttribute(): string
    {
        if ($this->relationLoaded('images') && $first = $this->images->first()) {
            return url('storage/' . $first->path);
        }

        // Fallback: busca do SeoSetting ou asset default
        return \App\Models\SeoSetting::getValue('og_image_default', asset('images/og-default.jpg'));
    }

    public function getSchemaMarkupAttribute(): string
    {
        return app(\App\Services\ProductService::class)->renderSchemaMarkup($this);
    }

    public function getGoogleTagManagerAttribute(): string
    {
        return app(\App\Services\ProductService::class)->renderGtmScript($this);
    }

    // ─── Boot, Casts, Relations, Scopes ──────────────────────────────

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Product $product) {
            if (empty($product->slug) && ! empty($product->name)) {
                $product->slug = static::generateUniqueSlug($product->name, $product->tenant_id);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('name') && ! $product->isDirty('slug')) {
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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id')
            ->withTimestamps();
    }

    public function hasAdultContent(): bool
    {
        return $this->categories()->whereAdultContent()->exists();
    }

    public function scopeWhereHasAdultCategories($query)
    {
        return $query->whereHas('categories', function ($q) {
            $q->whereAdultContent();
        });
    }

    public function scopeWithoutAdultCategories($query)
    {
        return $query->whereDoesntHave('categories', function ($q) {
            $q->whereAdultContent();
        });
    }

    /**
     * Scope: produtos disponíveis para venda na loja pública.
     *
     * Critérios cumulativos (todos devem ser atendidos):
     * 1. Produto ativo (is_active = true)
     * 2. Vendedor (tenant) ativo (tenants.active = true)
     * 3. Vendedor com e-mail verificado (users.email_verified_at IS NOT NULL)
     * 4. Pelo menos 1 transportadora ativa com contrato vinculado ao vendedor
     * 5. Pelo menos 1 transportadora vinculada com e-mail verificado
     */
    public function scopeAvailableForSale($query)
    {
        return $query
            ->where('is_active', true)
            ->whereHas('tenant', function ($q) {
                $q->where('active', true)
                  ->whereHas('user', function ($uq) {
                      $uq->whereNotNull('email_verified_at');
                  })
                  ->whereHas('carrierTenantAgreements', function ($aq) {
                      $aq->where('status', 'active')
                        ->whereHas('carrier', function ($cq) {
                            $cq->where('is_active', true)
                              ->whereHas('user', function ($cuq) {
                                  $cuq->whereNotNull('email_verified_at');
                              });
                        });
                  });
            });
    }

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
            if (! $query->exists()) {
                break;
            }
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}