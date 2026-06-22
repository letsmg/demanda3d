<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    public function list(int $perPage = 15)
    {
        return Product::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Product
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image_path'] = $this->uploadImage($data['image'], $data['tenant_id']);
        }

        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $this->uploadImage($data['image'], $product->tenant_id);
        }

        $product->update($data);

        return $product;
    }

    public function delete(Product $product): bool
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        return $product->delete();
    }

    /**
     * Get all active products for the store (no tenant filter).
     */
    public function listActiveForStore()
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    private function uploadImage(UploadedFile $image, int $tenantId): string
    {
        $path = $image->store("imgs/products/{$tenantId}", 'public');

        return Storage::url($path);
    }
}