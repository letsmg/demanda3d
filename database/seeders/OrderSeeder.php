<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $encrypt = fn ($v) => EncryptionService::encryptWithHash($v);

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('⚠ Nenhum tenant encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $descriptions = [
            $encrypt('Produção de 50 peças de engrenagens em PLA para protótipo industrial.'),
            $encrypt('Impressão de suportes personalizados para equipamentos de laboratório em ABS.'),
            $encrypt('Lote de 100 peças de acabamento automotivo em PETG, com tolerância de 0.1mm.'),
            $encrypt('Produção de 30 modelos arquitetônicos em escala 1:100 para apresentação comercial.'),
            $encrypt('Impressão de 20 peças estruturais em Nylon para teste de resistência mecânica.'),
        ];

        $totalOrders = 0;

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            $clients = Client::where('tenant_id', $tenantId)->get();

            if ($clients->isEmpty()) {
                $this->command->warn("  ⚠ Tenant #{$tenantId}: sem clients — orders não criadas.");
                continue;
            }

            // Produtos deste tenant (ProductSeeder garante que todo tenant tem produtos)
            $products = Product::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->get();

            if ($products->isEmpty()) {
                $this->command->warn("  ⚠ Tenant #{$tenantId}: sem products — orders não criadas.");
                continue;
            }

            foreach ($clients as $client) {
                $numOrders = min(random_int(1, 3), count($descriptions));

                for ($i = 0; $i < $numOrders; $i++) {
                    $desc = $descriptions[array_rand($descriptions)];
                    $product = $products->random();

                    Order::create([
                        'tenant_id' => $tenantId,
                        'client_id' => $client->id,
                        'product_id' => $product->id,
                        'order_date' => now()->subDays(random_int(5, 180)),
                        'delivery_date' => now()->addDays(random_int(7, 60)),
                        'price' => round(random_int(150, 800) + (random_int(0, 99) / 100), 2),
                        'contracted_description_encrypted' => $desc['encrypted'],
                        'contracted_description_hash' => $desc['hash'],
                        'status' => collect(['pending', 'in_progress', 'delivered'])->random(),
                    ]);

                    $totalOrders++;
                }
            }

            $this->command->info("  ✓ Tenant #{$tenantId}: {$clients->count()} client(s) × {$products->count()} product(s) — orders criadas.");
        }

        $this->command->info("✓ Total: {$totalOrders} pedidos criados com sucesso.");
    }
}