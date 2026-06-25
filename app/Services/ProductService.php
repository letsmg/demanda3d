<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProductService
{
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

        $product = Product::create($data);

        $this->syncImages($product, $data['images'] ?? []);

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

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
     * Sync product images: delete removed ones, upload new ones.
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