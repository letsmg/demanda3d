<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'client_id' => Client::factory(),
            'order_id' => fake()->optional(0.7)->passthrough(Order::factory()),
            'status' => fake()->randomElement(['open', 'open', 'open', 'closed', 'archived']),
        ];
    }
}