<?php

namespace Database\Seeders;

use App\Models\Input;
use App\Models\Supplier;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class InputSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute UserSeeder primeiro.');

            return;
        }

        $this->command->info('=== Criando insumos ===');

        $inputsData = [
            [
                'description' => 'Filamento PLA 1.75mm 1kg Preto',
                'brand' => '3DLab',
                'quantity' => 2000,
                'shipping_cost' => 15.90,
                'cost_value' => 89.90,
            ],
            [
                'description' => 'Filamento ABS 1.75mm 1kg Natural',
                'brand' => 'eSun',
                'quantity' => 1000,
                'shipping_cost' => 22.50,
                'cost_value' => 120.00,
            ],
            [
                'description' => 'Filamento PETG 1.75mm 500g Transparente',
                'brand' => 'Creality',
                'quantity' => 500,
                'shipping_cost' => 12.00,
                'cost_value' => 75.00,
            ],
            [
                'description' => 'TPU Flexível 1.75mm 250g Preto',
                'brand' => 'SUNLU',
                'quantity' => 250,
                'shipping_cost' => 18.00,
                'cost_value' => 135.00,
            ],
            [
                'description' => 'Filamento Nylon 1.75mm 500g Natural',
                'brand' => 'Polymaker',
                'quantity' => 500,
                'shipping_cost' => 28.00,
                'cost_value' => 210.00,
            ],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            $suppliers = Supplier::where('tenant_id', $tenantId)->get();

            if ($suppliers->isEmpty()) {
                $this->command->warn("  Tenant #{$tenantId}: sem suppliers — inputs não criadas.");

                continue;
            }

            foreach ($inputsData as $inputData) {
                $supplier = $suppliers->random();

                Input::create([
                    'tenant_id' => $tenantId,
                    'supplier_id' => $supplier->id,
                    'description' => $inputData['description'],
                    'brand' => $inputData['brand'],
                    'quantity' => $inputData['quantity'],
                    'shipping_cost' => $inputData['shipping_cost'],
                    'cost_value' => $inputData['cost_value'],
                ]);
            }

            $this->command->info("  ✓ Tenant #{$tenantId}: ".count($inputsData).' inputs criados.');
        }

        // Criar 5 inputs aleatórios extras via factory
        Input::factory()->count(5)->create();

        $total = Input::count();
        $this->command->info("✓ Total: {$total} insumos criados com sucesso.");
    }
}