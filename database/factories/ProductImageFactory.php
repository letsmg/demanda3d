<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductImageFactory extends Factory
{
    protected $model = ProductImage::class;

    public function definition(): array
    {
        // Use picsum.photos for placeholder images
        $imageId = $this->faker->numberBetween(1, 400);
        $path = 'products/placeholder/' . $this->faker->uuid() . '.jpg';

        return [
            'product_id' => Product::factory(),
            'path' => $path,
            'order' => 0,
        ];
    }
}