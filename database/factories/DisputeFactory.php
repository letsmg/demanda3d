<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class DisputeFactory extends Factory
{
    protected $model = Dispute::class;

    public function definition(): array
    {
        $reasons = ['fraud', 'fake_product', 'offensive', 'not_delivered'];

        return [
            'tenant_id' => Tenant::factory(),
            'reporter_id' => Client::factory(),
            'order_id' => fake()->optional(0.6)->passthrough(Order::factory()),
            'reason' => fake()->randomElement($reasons),
            'description_encrypted' => Crypt::encryptString(fake()->paragraph()),
            'status' => fake()->randomElement(['pending', 'pending', 'investigating', 'resolved', 'dismissed']),
            'admin_id' => fake()->optional(0.3)->passthrough(User::factory()->admin()),
        ];
    }
}