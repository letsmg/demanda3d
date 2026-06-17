<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'order_date' => fake()->dateTimeBetween('-6 months', '-1 month'),
            'delivery_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'price' => fake()->randomFloat(2, 50, 5000),
            'contracted_description' => fake()->realTextBetween(50, 200),
        ];
    }
}