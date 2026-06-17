<?php

namespace Database\Seeders;

use App\Models\Input;
use Illuminate\Database\Seeder;

class InputSeeder extends Seeder
{
    public function run(): void
    {
        Input::factory()->create([
            'filaments' => 'PLA 1.75mm',
            'energy' => 450.75,
            'dt_buy' => now()->subMonths(2),
            'cost_buy' => 189.90,
            'purge' => 5.5,
        ]);

        Input::factory()->create([
            'filaments' => 'ABS 1.75mm',
            'energy' => 380.00,
            'dt_buy' => now()->subMonths(1),
            'cost_buy' => 245.00,
            'purge' => 8.2,
        ]);

        Input::factory()->create([
            'filaments' => 'PETG 1.75mm',
            'energy' => 520.30,
            'dt_buy' => now()->subDays(45),
            'cost_buy' => 298.50,
            'purge' => 3.0,
        ]);

        Input::factory()->create([
            'filaments' => 'TPU 1.75mm Flexível',
            'energy' => 290.00,
            'dt_buy' => now()->subWeeks(3),
            'cost_buy' => 350.00,
            'purge' => 6.8,
        ]);

        Input::factory()->create([
            'filaments' => 'Nylon 1.75mm',
            'energy' => 610.50,
            'dt_buy' => now()->subDays(10),
            'cost_buy' => 420.00,
            'purge' => 12.0,
        ]);

        Input::factory()->count(10)->create();
    }
}