<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\Review;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'client_id' => Client::factory(),
            'order_id' => Order::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment_encrypted' => Crypt::encryptString(fake()->sentence()),
        ];
    }

    public function withTenant(int $tenantId): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenantId,
        ]);
    }
}