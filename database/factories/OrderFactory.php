<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $desc = $this->faker->sentence(10);
        $descResult = EncryptionService::encryptWithHash($desc);

        $client = Client::inRandomOrder()->first();

        // Buscar um produto do mesmo tenant do client
        $product = $client
            ? Product::where('tenant_id', $client->tenant_id)->inRandomOrder()->first()
            : Product::inRandomOrder()->first();

        return [
            'tenant_id' => $client?->tenant_id ?? 1,
            'client_id' => $client?->id ?? 1,
            'product_id' => $product?->id ?? 1,
            'order_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'contracted_description_encrypted' => $descResult['encrypted'],
            'contracted_description_hash' => $descResult['hash'],
        ];
    }
}