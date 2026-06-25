<?php

namespace Database\Factories;

use App\Models\Input;
use App\Models\Supplier;
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
        $materials = ['PLA', 'ABS', 'PETG', 'TPU', 'Nylon', 'Polycarbonate', 'PVA', 'HIPS', 'Resina UV', 'Resina Standard'];
        $brands = ['3DLab', 'eSun', 'Creality', 'SUNLU', 'Polymaker', 'FiloPrint', 'Anycubic', 'Elegoo'];

        return [
            'tenant_id' => Tenant::inRandomOrder()->first()?->id ?? 1,
            'supplier_id' => Supplier::inRandomOrder()->first()?->id ?? 1,
            'description' => fake()->randomElement($materials) . ' ' . fake()->randomFloat(1, 1.75, 2.85) . 'mm ' . fake()->randomElement(['1kg', '500g', '250g']),
            'brand' => fake()->randomElement($brands),
            'purchase_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'quantity' => fake()->numberBetween(500, 5000),
            'shipping_cost' => fake()->randomFloat(2, 10, 50),
            'cost_value' => fake()->randomFloat(2, 50, 500),
        ];
    }
}