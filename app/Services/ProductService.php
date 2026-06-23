<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
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
            $query->where('price_sale', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_sale', '<=', $filters['max_price']);
        }

        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $allowedSorts = ['name', 'price_sale', 'created_at'];
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
        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->with(['images', 'tenant']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price_sale', '>=', (float) $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_sale', '<=', (float) $filters['max_price']);
        }

        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';

        $allowedSorts = ['name', 'price_sale', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        return $query->get();
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