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
        $client = Client::inRandomOrder()->first();

        return [
            'tenant_id'        => $client?->tenant_id ?? 1,
            'client_id'        => $client?->id ?? 1,
            'order_date'       => $this->faker->date(),
            'delivery_date'    => $this->faker->date(),
            'stripe_session_id' => null,
            'amount_total'     => $this->faker->randomFloat(2, 100, 10000),
            'currency'         => 'brl',
            'status'           => 'delivered',
        ];
    }
}