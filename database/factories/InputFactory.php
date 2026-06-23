<?php

namespace Database\Factories;

use App\Models\Input;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Input>
 */
class InputFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filamentTypes = ['PLA', 'ABS', 'PETG', 'TPU', 'Nylon', 'Polycarbonate', 'PVA', 'HIPS'];

        return [
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? 1,
            'filaments' => fake()->randomElement($filamentTypes) . ' ' . fake()->randomFloat(1, 1.75, 2.85) . 'mm',
            'energy' => fake()->randomFloat(2, 100, 2000),
            'dt_buy' => fake()->dateTimeBetween('-3 months', 'now'),
            'cost_buy' => fake()->randomFloat(2, 50, 500),
            'purge' => fake()->randomFloat(2, 0, 50),
        ];
    }
}