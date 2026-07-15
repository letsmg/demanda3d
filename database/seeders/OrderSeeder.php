<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('⚠ Nenhum tenant encontrado. Execute UserSeeder primeiro.');
            return;
        }

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
                $numOrders = min(random_int(1, 3), 3);

                for ($i = 0; $i < $numOrders; $i++) {
                    $product = $products->random();

                    $order = Order::firstOrCreate(
                        [
                            'tenant_id' => $tenantId,
                            'client_id' => $client->id,
                            'order_date' => now()->subDays(random_int(5, 180)),
                        ],
                        [
                            'delivery_date' => now()->addDays(random_int(7, 60)),
                            'status' => collect(['pending', 'in_progress', 'delivered'])->random(),
                        ]
                    );

                    // Cria o OrderItem com snapshot imutável do produto
                    OrderItem::firstOrCreate(
                        [
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                        ],
                        [
                            'snapshot_product_name'  => $product->name,
                            'snapshot_product_price' => (float) $product->sale_price,
                            'quantity'               => random_int(1, 5),
                        ]
                    );

                    $totalOrders++;
                }
            }

            $this->command->info("  ✓ Tenant #{$tenantId}: {$clients->count()} client(s) × {$products->count()} product(s) — orders criadas.");
        }

        $this->command->info("✓ Total: {$totalOrders} pedidos criados com sucesso.");
    }
}