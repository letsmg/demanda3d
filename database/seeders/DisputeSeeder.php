<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Client;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Seeder;

class DisputeSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $this->command->info('=== Criando denúncias/litígios ===');

        $reasons = ['fraud', 'fake_product', 'offensive', 'not_delivered'];
        $statuses = ['pending', 'pending', 'investigating', 'resolved', 'dismissed'];

        $adminUsers = User::where('access_level', UserAccessLevel::ADMIN)->get();
        $encrypt = fn ($v) => Crypt::encryptString($v);

        $descriptions = [
            'fraud' => [
                'O produto anunciado não corresponde ao que foi entregue. O material é de qualidade inferior.',
                'A peça veio com defeitos graves de impressão, diferente do prometido no anúncio.',
            ],
            'fake_product' => [
                'Recebi uma peça que claramente não é de impressão 3D, parece ser produção em massa.',
                'O material utilizado não é ABS conforme anunciado, aparenta ser plástico comum.',
            ],
            'offensive' => [
                'O vendedor utilizou linguagem ofensiva durante a negociação do pedido.',
                'Fui tratado com desrespeito ao questionar o prazo de entrega.',
            ],
            'not_delivered' => [
                'O pedido foi marcado como entregue mas nunca recebi o produto.',
                'Prazo de entrega expirou há 15 dias e não recebi nenhuma atualização.',
                'A transportadora informa que o pacote foi extraviado e o vendedor não responde.',
            ],
        ];

        $total = 0;

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            $clients = Client::where('tenant_id', $tenantId)->get();

            if ($clients->isEmpty()) {
                continue;
            }

            $orders = Order::where('tenant_id', $tenantId)->get();

            // Criar 1-3 disputas por tenant
            $numDisputes = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $numDisputes; $i++) {
                $client = $clients->random();
                $reason = fake()->randomElement($reasons);
                $descriptionText = fake()->randomElement($descriptions[$reason]);

                // 70% chance de ter order vinculada
                $orderId = ($orders->isNotEmpty() && fake()->boolean(70))
                    ? $orders->random()->id
                    : null;

                // 30% dos casos já têm admin vinculado
                $adminId = ($adminUsers->isNotEmpty() && fake()->boolean(30))
                    ? $adminUsers->random()->id
                    : null;

                Dispute::create([
                    'tenant_id' => $tenantId,
                    'reporter_id' => $client->id,
                    'order_id' => $orderId,
                    'reason' => $reason,
                    'description_encrypted' => $encrypt($descriptionText),
                    'status' => $adminId ? fake()->randomElement(['investigating', 'resolved']) : 'pending',
                    'admin_id' => $adminId,
                ]);

                $total++;
            }

            $count = Dispute::where('tenant_id', $tenantId)->count();
            $this->command->info("  ✓ Tenant #{$tenantId}: {$count} disputas criadas.");
        }

        $this->command->info("✓ Total: {$total} denúncias/litígios criados com sucesso.");
    }
}