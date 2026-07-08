<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private readonly ImageModerationService $imageModerationService;
    private readonly ImageOptimizationService $imageOptimizationService;

    public function __construct(
        ?ImageModerationService $imageModerationService = null,
        ?ImageOptimizationService $imageOptimizationService = null,
    ) {
        $this->imageModerationService = $imageModerationService ?? app(ImageModerationService::class);
        $this->imageOptimizationService = $imageOptimizationService ?? app(ImageOptimizationService::class);
    }

    public function list(int $perPage = 15, array $filters = [])
    {
        $query = Product::query();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', $filters['max_price']);
        }

        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Product
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        $categories = $data['categories'] ?? [];
        unset($data['categories']);

        $product = Product::create($data);

        if (!empty($categories)) {
            $product->categories()->sync($categories);
        }

        $this->syncImages($product, $data['images'] ?? []);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $categories = $data['categories'] ?? null;
        $imagesDelete = $data['images_delete'] ?? [];
        $imagesOrder = $data['images_order'] ?? null;
        unset($data['categories'], $data['images_delete'], $data['images_order']);

        $product->update($data);

        if ($categories !== null) {
            $product->categories()->sync($categories);
        }

        // Processa exclusão de imagens
        if (!empty($imagesDelete)) {
            $this->deleteImages($product, $imagesDelete);
        }

        // Processa reordenação de imagens
        if ($imagesOrder !== null) {
            $this->reorderImages($product, $imagesOrder);
        }

        // Upload de novas imagens
        if (isset($data['images']) && !empty($data['images'])) {
            $this->syncImages($product, $data['images']);
        }

        return $product;
    }

    /**
     * Remove imagens do produto (storage + banco).
     */
    private function deleteImages(Product $product, array $imageIds): void
    {
        $images = $product->images()->whereIn('id', $imageIds)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            if ($image->original_path) {
                Storage::disk('public')->delete($image->original_path);
            }
            if ($image->thumbnail_path) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
            $image->delete();
        }
    }

    /**
     * Reordena as imagens do produto conforme array de IDs na nova ordem.
     */
    private function reorderImages(Product $product, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['order' => $index]);
        }
    }

    public function delete(Product $product): bool
    {
        // Delete all images (optimized, original, thumbnail)
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            if ($image->original_path) {
                Storage::disk('public')->delete($image->original_path);
            }
            if ($image->thumbnail_path) {
                Storage::disk('public')->delete($image->thumbnail_path);
            }
            $image->delete();
        }

        return $product->delete();
    }

    /**
     * Get all active products for the public store.
     * Uses withoutGlobalScopes so all tenants' products are visible.
     */
    public function listActiveForStore(array $filters = [], bool $canViewAdult = false)
    {
        $searchTerm = $filters['search'] ?? '';
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        // Cache apenas quando NÃO há filtro de categoria, preço ou sort customizado
        $useCache = !empty($searchTerm) && strlen($searchTerm) >= 3
                    && empty($minPrice) && empty($maxPrice)
                    && empty($filters['category'])
                    && $sortField === 'name' && $sortDir === 'asc';

        if ($useCache) {
            $cacheKey = 'store:search:' . hash('sha256', strtolower(trim($searchTerm)));
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        // --- Build query ---
        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->whereHas('tenant', function ($q) {
                $q->whereHas('user', function ($sq) {
                    $sq->whereHas('vendorCarriers', function ($vc) {
                        $vc->where('status', 'approved');
                    });
                });
            })
            ->with(['images', 'tenant.user', 'categories']);

        // Filtro de conteúdo adulto
        if (! $canViewAdult) {
            $query->withoutAdultCategories();
        }

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', "%{$searchTerm}%")
                  ->orWhere('description', 'ilike', "%{$searchTerm}%");
            });
        }

        if (!empty($minPrice)) {
            $query->where('sale_price', '>=', (float) $minPrice);
        }

        if (!empty($maxPrice)) {
            $query->where('sale_price', '<=', (float) $maxPrice);
        }

        // Filtro por categoria
        if (!empty($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $results = $query->take(8)->get();

        // --- Store in cache for future searches ---
        if ($useCache && isset($cacheKey)) {
            Cache::put($cacheKey, $results, now()->addMinutes(10));
        }

        return $results;
    }

    /**
     * Build the base query for active store products (shared by listActiveForStore and paginateActiveForStore).
     */
    private function buildActiveStoreQuery(array $filters = [], bool $canViewAdult = false)
    {
        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->whereHas('tenant', function ($q) {
                $q->whereHas('user', function ($sq) {
                    $sq->whereHas('vendorCarriers', function ($vc) {
                        $vc->where('status', 'approved');
                    });
                });
            })
            ->with(['images', 'tenant.user', 'categories']);

        if (! $canViewAdult) {
            $query->withoutAdultCategories();
        }

        $searchTerm = $filters['search'] ?? '';
        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', "%{$searchTerm}%")
                  ->orWhere('description', 'ilike', "%{$searchTerm}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('sale_price', '>=', (float) $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('sale_price', '<=', (float) $filters['max_price']);
        }

        if (!empty($filters['category'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query;
    }

    /**
     * Paginate active products for the store API (lazy loading / "mostrar mais").
     *
     * @return array{data: \Illuminate\Database\Eloquent\Collection, has_more: bool, total: int}
     */
    public function paginateActiveForStore(array $filters = [], bool $canViewAdult = false, int $page = 1, int $perPage = 10): array
    {
        $query = $this->buildActiveStoreQuery($filters, $canViewAdult);

        $total = (clone $query)->count();
        $offset = ($page - 1) * $perPage;

        $results = $query->skip($offset)->take($perPage)->get();

        return [
            'data' => $results,
            'has_more' => ($offset + $perPage) < $total,
            'total' => $total,
        ];
    }

    /**
     * Lista produtos ativos para a API pública.
     * Aplica filtro de conteúdo adulto baseado na permissão do usuário.
     */
    public function listActiveForApi(array $filters, bool $canViewAdult = false)
    {
        $searchTerm = $filters['search'] ?? '';
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $categorySlug = $filters['category'] ?? null;

        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->with(['images', 'categories']);

        // Filtro de conteúdo adulto
        if (!$canViewAdult) {
            $query->withoutAdultCategories();
        }

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', "%{$searchTerm}%")
                  ->orWhere('description', 'ilike', "%{$searchTerm}%");
            });
        }

        if (!empty($minPrice)) {
            $query->where('sale_price', '>=', (float) $minPrice);
        }

        if (!empty($maxPrice)) {
            $query->where('sale_price', '<=', (float) $maxPrice);
        }

        if (!empty($categorySlug)) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->get();
    }

    /**
     * Determina se a moderação automática de imagem deve ser ignorada.
     *
     * Se o produto pertence à categoria 'adulto', a moderação (ex: Google SafeSearch)
     * deve ser ignorada, pois o conteúdo adulto é permitido nessa categoria específica.
     */
    public function shouldSkipImageModeration(Product $product): bool
    {
        return $product->categories()->where('slug', 'adulto')->exists();
    }

    /**
     * Gera automaticamente campos SEO com base no nome, descrição e categorias do produto.
     *
     * - Só preenche campos que não foram explicitamente informados pelo usuário.
     * - No update, recalcula apenas campos cujo valor atual é igual ao auto-gerado anteriormente
     *   ou que estão vazios, evitando sobrescrever personalizações manuais do usuário.
     *
     * @param  array         $data        Dados recebidos do formulário
     * @param  Product|null  $existing    Produto existente (null para criação)
     * @return array                      Dados com campos SEO auto-gerados
     */
    /**
     * Sobrecarga pública para o accessor do Model. Aceita apenas o Product
     * e extrai todos os dados necessários dos atributos e relacionamentos.
     */
    public function renderSchemaMarkup(Product $product): string
    {
        $data = $product->getAttributes();
        $categoryString = $product->categories()->pluck('name')->implode(', ');

        return $this->generateSchemaMarkupInternal($data, $product, $categoryString);
    }

    /**
     * Gera schema markup JSON-LD para Product (schema.org).
     *
     * Inclui: nome, descrição, imagem, preço, disponibilidade, categorias,
     * marca (tenant), SKU e dimensões quando disponíveis.
     */
    private function generateSchemaMarkupInternal(array $data, ?Product $existing, string $categoryString): string
    {
        $name = $data['name'] ?? ($existing?->name ?? 'Produto');
        $description = trim(strip_tags($data['description'] ?? ($existing?->description ?? '')));
        $salePrice = $data['sale_price'] ?? ($existing?->sale_price ?? 0);
        $slug = $data['slug'] ?? ($existing?->slug ?? '');
        $tenantName = '';
        $imageUrl = '';

        if ($existing) {
            $tenantName = $existing->tenant?->display_name ?? '';
            if ($firstImage = $existing->firstImage()) {
                $imageUrl = url('storage/' . $firstImage->path);
            }
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $name,
            'description' => mb_substr($description ?: $name, 0, 5000),
            'sku' => $slug ?: 'PROD-' . ($existing?->id ?? 'new'),
            'offers' => [
                '@type' => 'Offer',
                'price' => (string) $salePrice,
                'priceCurrency' => 'BRL',
                'availability' => 'https://schema.org/InStock',
                'url' => $slug ? route('store.detail', ['slug' => $slug]) : '',
                'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            ],
        ];

        if (!empty($imageUrl)) {
            $schema['image'] = $imageUrl;
        }

        if (!empty($tenantName)) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $tenantName,
            ];
        }

        if (!empty($categoryString)) {
            $schema['category'] = $categoryString;
        }

        // Adiciona dimensões se disponíveis
        $height = $data['height'] ?? ($existing?->height ?? null);
        $width = $data['width'] ?? ($existing?->width ?? null);
        $weight = $data['approximate_weight'] ?? ($existing?->approximate_weight ?? null);

        if ($height || $width || $weight) {
            $schema['additionalProperty'] = [];
            if ($height) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Altura',
                    'value' => $height . ' mm',
                ];
            }
            if ($width) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Largura',
                    'value' => $width . ' mm',
                ];
            }
            if ($weight) {
                $schema['additionalProperty'][] = [
                    '@type' => 'PropertyValue',
                    'name' => 'Peso Aproximado',
                    'value' => $weight . ' g',
                ];
            }
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Sobrecarga pública para o accessor do Model. Aceita apenas o Product
     * e extrai todos os dados necessários dos atributos.
     */
    public function renderGtmScript(Product $product): string
    {
        return $this->generateGtmScriptInternal($product->getAttributes(), $product);
    }

    /**
     * Gera script de Google Tag Manager com dataLayer para o produto.
     *
     * Configura evento de page_view com dados do produto para remarketing
     * e trackeamento de e-commerce no Google Analytics 4.
     */
    private function generateGtmScriptInternal(array $data, ?Product $existing): string
    {
        $name = $data['name'] ?? ($existing?->name ?? 'Produto');
        $salePrice = $data['sale_price'] ?? ($existing?->sale_price ?? 0);
        $slug = $data['slug'] ?? ($existing?->slug ?? '');
        $productId = $existing?->id ?? '';

        $dataLayer = [
            'event' => 'product_detail_view',
            'ecommerce' => [
                'detail' => [
                    'products' => [[
                        'id' => (string) $productId,
                        'name' => $name,
                        'price' => (string) $salePrice,
                        'variant' => $slug,
                    ]],
                ],
            ],
        ];

        $dataLayerJson = json_encode($dataLayer, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return <<<HTML
<!-- Google Tag Manager -->
<script>
  window.dataLayer = window.dataLayer || [];
  dataLayer.push({$dataLayerJson});
</script>
<!-- End Google Tag Manager -->
HTML;
    }

    /**
     * Sincroniza imagens do produto com pipeline completo:
     * 1. Moderação via Google Cloud Vision SafeSearch
     * 2. Upload físico (original + thumbnail + optimized) em paths por tenant
     * 3. Persistência no banco
     *
     * Tudo dentro de DB::transaction — se o upload falhar, o banco reverte.
     *
     * @throws \RuntimeException Se conteúdo ilegal for detectado (422).
     */
    private function syncImages(Product $product, array $images): void
    {
        $order = 0;
        $maxImages = 5;

        $currentCount = $product->images()->count();
        $remainingSlots = $maxImages - $currentCount;

        if ($remainingSlots <= 0) {
            return;
        }

        foreach ($images as $image) {
            if ($image instanceof UploadedFile && $order < $remainingSlots) {
                if ($image->getSize() > 2 * 1024 * 1024) {
                    Log::warning('Imagem excede 2MB, ignorada no syncImages.', [
                        'product_id' => $product->id,
                        'size' => $image->getSize(),
                    ]);
                    continue;
                }

                // 1. Moderação — Google Cloud Vision SafeSearch (block se ilegal)
                try {
                    $moderationResult = $this->imageModerationService->moderateUpload($image, $product);

                    if ($moderationResult['adult_category_id']) {
                        $currentCategories = $product->categories()->pluck('id')->toArray();
                        if (!in_array($moderationResult['adult_category_id'], $currentCategories, true)) {
                            $currentCategories[] = $moderationResult['adult_category_id'];
                            $product->categories()->sync($currentCategories);
                        }
                    }
                } catch (\RuntimeException $e) {
                    throw $e; // Conteúdo ilegal — 422
                }

                // 2. Upload + otimização dentro de transaction
                DB::transaction(function () use ($product, $image, &$order, $currentCount) {
                    $result = $this->imageOptimizationService->processProductUpload(
                        $image,
                        $product->tenant_id,
                        $product->id,
                        $product->slug,
                    );

                    // 3. Persiste no banco APÓS sucesso do upload
                    ProductImage::create([
                        'product_id' => $product->id,
                        'path' => $result['optimized_path'],
                        'original_path' => $result['original_path'],
                        'thumbnail_path' => $result['thumbnail_path'],
                        'order' => $currentCount + $order,
                    ]);

                    Log::info('Imagem de produto processada com pipeline completo.', [
                        'product_id' => $product->id,
                        'tenant_id' => $product->tenant_id,
                        'optimized_path' => $result['optimized_path'],
                        'thumbnail_path' => $result['thumbnail_path'],
                    ]);
                });

                $order++;
            }
        }
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.