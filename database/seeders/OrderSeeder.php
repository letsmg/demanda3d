<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $encrypt = fn ($v) => EncryptionService::encryptWithHash($v);

        // Buscar clients pelos e-mails fixos do ClientSeeder
        $tech3d = Client::where('email', 'tech3d@demanda3d.com')->first();
        $prototipagem = Client::where('email', 'prototipagem@demanda3d.com')->first();

        if (!$tech3d || !$prototipagem) {
            $this->command->warn('⚠ Clients fixos não encontrados. Execute ClientSeeder primeiro.');
            return;
        }

        $d1 = $encrypt('Produção de 50 peças de engrenagens em PLA para protótipo industrial.');
        $d2 = $encrypt('Impressão de suportes personalizados para equipamentos de laboratório em ABS.');
        $d3 = $encrypt('Lote de 100 peças de acabamento automotivo em PETG, com tolerância de 0.1mm.');
        $d4 = $encrypt('Produção de 30 modelos arquitetônicos em escala 1:100 para apresentação comercial.');
        $d5 = $encrypt('Impressão de 20 peças estruturais em Nylon para teste de resistência mecânica.');

        // Orders for Tech3D (client 1)
        Order::factory()->count(3)->create([
            'client_id' => $tech3d->id,
            'tenant_id' => $tech3d->tenant_id,
            'order_date' => now()->subMonths(5),
            'delivery_date' => now()->subMonths(4),
            'price' => 2500.00,
            'contracted_description_encrypted' => $d1['encrypted'],
            'contracted_description_hash' => $d1['hash'],
        ]);

        Order::factory()->create([
            'client_id' => $tech3d->id,
            'tenant_id' => $tech3d->tenant_id,
            'order_date' => now()->subMonths(2),
            'delivery_date' => now()->subMonth(),
            'price' => 1800.50,
            'contracted_description_encrypted' => $d2['encrypted'],
            'contracted_description_hash' => $d2['hash'],
        ]);

        Order::factory()->create([
            'client_id' => $tech3d->id,
            'tenant_id' => $tech3d->tenant_id,
            'order_date' => now()->subDays(15),
            'delivery_date' => now()->addDays(15),
            'price' => 4200.00,
            'contracted_description_encrypted' => $d3['encrypted'],
            'contracted_description_hash' => $d3['hash'],
        ]);

        // Orders for Prototipagem Rápida (client 2)
        Order::factory()->create([
            'client_id' => $prototipagem->id,
            'tenant_id' => $prototipagem->tenant_id,
            'order_date' => now()->subMonths(3),
            'delivery_date' => now()->subMonths(2),
            'price' => 5800.75,
            'contracted_description_encrypted' => $d4['encrypted'],
            'contracted_description_hash' => $d4['hash'],
        ]);

        Order::factory()->create([
            'client_id' => $prototipagem->id,
            'tenant_id' => $prototipagem->tenant_id,
            'order_date' => now()->subDays(5),
            'delivery_date' => now()->addMonth(),
            'price' => 3200.00,
            'contracted_description_encrypted' => $d5['encrypted'],
            'contracted_description_hash' => $d5['hash'],
        ]);

        // Random orders for random clients
        $clients = Client::all();
        if ($clients->isNotEmpty()) {
            Order::factory()->count(10)->create([
                'client_id' => fn () => $clients->random()->id,
                'tenant_id' => fn () => $clients->random()->tenant_id,
            ]);
        }

        $this->command->info('✓ Pedidos criados com sucesso.');
    }
}