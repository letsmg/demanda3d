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
        $materialType = $this->faker->randomElement(['filament', 'resin']);
        $wasteWeight = $this->faker->numberBetween(5, 80);
        $approximateWeight = $this->faker->numberBetween(10, 500);
        $printTime = $this->faker->numberBetween(15, 1440);
        $paintingTime = $this->faker->optional(0.4)->numberBetween(10, 180);
        $hasPainting = $paintingTime !== null;
        $paintingCost = $hasPainting ? $this->faker->randomFloat(2, 2, 50) : 0;
        $maintenanceFee = $this->faker->randomFloat(2, 0.50, 15);
        $extrasCost = $this->faker->randomFloat(2, 0, 20);
        $approximateCost = round(
            $maintenanceFee + $paintingCost + $extrasCost + $this->faker->randomFloat(2, 0.5, 30),
            2
        );

        return [
            'tenant_id' => Tenant::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'height' => $this->faker->numberBetween(5, 300),
            'width' => $this->faker->numberBetween(5, 300),
            'approximate_weight' => $approximateWeight,
            'waste_weight' => $wasteWeight,
            'material_type' => $materialType,
            'print_time' => $printTime,
            'pieces_produced' => $this->faker->numberBetween(1, 20),
            'maintenance_fee' => $maintenanceFee,
            'painting_time' => $paintingTime,
            'painting_material' => $hasPainting ? $this->faker->randomElement(['Tinta acrílica', 'Spray primer', 'Esmalte sintético', 'Tinta PU']) : null,
            'painting_cost' => $paintingCost,
            'extras_cost' => $extrasCost,
            'approximate_cost' => $approximateCost,
            'sale_price' => round($approximateCost * $this->faker->randomFloat(2, 2.0, 4.5), 2),
            'is_active' => true,
        ];
    }
}