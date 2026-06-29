<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageModerationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private readonly ImageModerationService $imageModerationService;

    public function __construct(
        ?ImageModerationService $imageModerationService = null,
    ) {
        $this->imageModerationService = $imageModerationService ?? app(ImageModerationService::class);
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

        $categorias = $data['categorias'] ?? [];
        unset($data['categorias']);

        $product = Product::create($data);

        if (!empty($categorias)) {
            $product->categorias()->sync($categorias);
        }

        $this->syncImages($product, $data['images'] ?? []);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $categorias = $data['categorias'] ?? null;
        unset($data['categorias']);

        $product->update($data);

        if ($categorias !== null) {
            $product->categorias()->sync($categorias);
        }

        if (isset($data['images'])) {
            $this->syncImages($product, $data['images']);
        }

        return $product;
    }

    public function delete(Product $product): bool
    {
        // Delete all images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        return $product->delete();
    }

    /**
     * Get all active products for the public store.
     * Uses withoutGlobalScopes so all tenants' products are visible.
     */
    public function listActiveForStore(array $filters = [])
    {
        $searchTerm = $filters['search'] ?? '';
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        // --- Cache strategy for search-only queries (no price/sort filters) ---
        $useCache = !empty($searchTerm) && strlen($searchTerm) >= 3
                    && empty($minPrice) && empty($maxPrice)
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
            ->with(['images', 'tenant.user']);

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

        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $results = $query->get();

        // --- Store in cache for future searches ---
        if ($useCache && isset($cacheKey)) {
            Cache::put($cacheKey, $results, now()->addMinutes(10));
        }

        return $results;
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
        $categoriaSlug = $filters['categoria'] ?? null;

        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->with(['images', 'categorias']);

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

        if (!empty($categoriaSlug)) {
            $query->whereHas('categorias', function ($q) use ($categoriaSlug) {
                $q->where('slug', $categoriaSlug);
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
        return $product->categorias()->where('slug', 'adulto')->exists();
    }

    /**
     * Sync product images: delete removed ones, upload new ones.
     * Integra moderação inteligente de imagem via ImageModerationService.
     *
     * @throws \RuntimeException Se conteúdo ilegal for detectado (422).
     */
    private function syncImages(Product $product, array $images): void
    {
        $order = 0;
        $maxImages = 5;

        // Remove excess images if more than allowed
        $currentCount = $product->images()->count();
        $remainingSlots = $maxImages - $currentCount;

        if ($remainingSlots <= 0) {
            return;
        }

        foreach ($images as $image) {
            if ($image instanceof UploadedFile && $order < $remainingSlots) {
                // Validate max size (4MB)
                if ($image->getSize() > 4 * 1024 * 1024) {
                    continue;
                }

                // Moderação inteligente da imagem
                try {
                    $moderationResult = $this->imageModerationService->moderateUpload($image, $product);

                    // Se conteúdo adulto detectado, vincula à categoria 'adulto'
                    if ($moderationResult['adult_category_id']) {
                        $currentCategorias = $product->categorias()->pluck('categoria_id')->toArray();
                        if (!in_array($moderationResult['adult_category_id'], $currentCategorias, true)) {
                            $currentCategorias[] = $moderationResult['adult_category_id'];
                            $product->categorias()->sync($currentCategorias);
                            Log::info('Categoria adulto vinculada automaticamente via moderação.', [
                                'product_id' => $product->id,
                            ]);
                        }
                    }

                    // Se classificação incerta, marca produto como pendente de revisão
                    if ($moderationResult['status'] === 'approved' && str_contains($moderationResult['category']->value, 'safe')) {
                        // Verifica se houve incerteza na classificação
                        if (str_contains($product->moderation_status ?? '', 'pending')) {
                            $product->update(['moderation_status' => 'pending_review']);
                        }
                    }
                } catch (\RuntimeException $e) {
                    // Conteúdo ilegal — relança a exceção para o controller tratar
                    throw $e;
                }

                $path = $image->store("imgs/products/{$product->tenant_id}", 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $path,
                    'order' => $currentCount + $order++,
                ]);
            }
        }
    }
}