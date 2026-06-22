<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $desc = $this->faker->sentence(10);
        $descResult = EncryptionService::encryptWithHash($desc);

        return [
            'tenant_id' => Tenant::factory(),
            'client_id' => Client::factory(),
            'order_date' => $this->faker->date(),
            'delivery_date' => $this->faker->date(),
            'price' => $this->faker->randomFloat(2, 100, 10000),
            'contracted_description_encrypted' => $descResult['encrypted'],
            'contracted_description_hash' => $descResult['hash'],
        ];
    }
}