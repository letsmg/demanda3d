<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'price_sale' => $this->faker->randomFloat(2, 10, 500),
            'discount_cash' => $this->faker->randomFloat(2, 0, 30),
            'image_path' => null,
            'is_active' => true,
        ];
    }
}