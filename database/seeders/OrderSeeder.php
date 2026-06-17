<?php

namespace Database\Seeders;

use App\Models\Order;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Orders for first client (Tech3D) - more orders, historical
        Order::factory()->count(3)->create([
            'client_id' => 1,
            'order_date' => now()->subMonths(5),
            'delivery_date' => now()->subMonths(4),
            'price' => 2500.00,
            'contracted_description' => 'Produção de 50 peças de engrenagens em PLA para protótipo industrial.',
        ]);

        Order::factory()->create([
            'client_id' => 1,
            'order_date' => now()->subMonths(2),
            'delivery_date' => now()->subMonth(),
            'price' => 1800.50,
            'contracted_description' => 'Impressão de suportes personalizados para equipamentos de laboratório em ABS.',
        ]);

        Order::factory()->create([
            'client_id' => 1,
            'order_date' => now()->subDays(15),
            'delivery_date' => now()->addDays(15),
            'price' => 4200.00,
            'contracted_description' => 'Lote de 100 peças de acabamento automotivo em PETG, com tolerância de 0.1mm.',
        ]);

        // Orders for second client (Prototipagem Rápida)
        Order::factory()->create([
            'client_id' => 2,
            'order_date' => now()->subMonths(3),
            'delivery_date' => now()->subMonths(2),
            'price' => 5800.75,
            'contracted_description' => 'Produção de 30 modelos arquitetônicos em escala 1:100 para apresentação comercial.',
        ]);

        Order::factory()->create([
            'client_id' => 2,
            'order_date' => now()->subDays(5),
            'delivery_date' => now()->addMonth(),
            'price' => 3200.00,
            'contracted_description' => 'Impressão de 20 peças estruturais em Nylon para teste de resistência mecânica.',
        ]);

        // Random orders for remaining clients
        Order::factory()->count(15)->create();
    }
}