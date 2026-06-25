<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\Thread;
use Illuminate\Database\Seeder;

class ThreadSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $this->command->info('=== Criando tópicos de conversa ===');

        $statuses = ['open', 'open', 'open', 'closed', 'archived'];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            $clients = Client::where('tenant_id', $tenantId)->get();

            if ($clients->isEmpty()) {
                $this->command->warn("  Tenant #{$tenantId}: sem clients — threads não criadas.");
                continue;
            }

            $orders = Order::where('tenant_id', $tenantId)->get();

            // Para cada client, criar 1-2 threads
            foreach ($clients as $client) {
                $numThreads = fake()->numberBetween(1, 2);

                for ($i = 0; $i < $numThreads; $i++) {
                    // 70% chance de ter order vinculada
                    $orderId = ($orders->isNotEmpty() && fake()->boolean(70))
                        ? $orders->random()->id
                        : null;

                    Thread::create([
                        'tenant_id' => $tenantId,
                        'client_id' => $client->id,
                        'order_id' => $orderId,
                        'status' => fake()->randomElement($statuses),
                    ]);
                }
            }

            $count = Thread::where('tenant_id', $tenantId)->count();
            $this->command->info("  ✓ Tenant #{$tenantId}: {$count} threads criadas.");
        }

        $total = Thread::count();
        $this->command->info("✓ Total: {$total} tópicos de conversa criados com sucesso.");
    }
}