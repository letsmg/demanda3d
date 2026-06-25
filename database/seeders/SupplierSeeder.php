<?php

namespace Database\Seeders;

use App\Models\Supplier;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $encrypt = fn ($v) => EncryptionService::encryptWithHash($v);

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $this->command->info('=== Criando fornecedores ===');

        $suppliersData = [
            [
                'name' => '3DLab Filamentos Ltda',
                'document' => '12.345.678/0001-90',
                'contact' => '(11) 4000-1001 / vendas@3dlab.com.br',
            ],
            [
                'name' => 'eSun Brasil Importação',
                'document' => '98.765.432/0001-10',
                'contact' => '(21) 3500-2002 / contato@esunbrasil.com',
            ],
            [
                'name' => 'Creality Supply Chain',
                'document' => '45.678.901/0001-23',
                'contact' => '(48) 3200-3003 / pedidos@crealitysupply.com.br',
            ],
            [
                'name' => 'Polymaker Indústria Química',
                'document' => '67.890.123/0001-45',
                'contact' => '(31) 3100-4004 / vendas@polymaker.ind.br',
            ],
            [
                'name' => 'SUNLU Distribuidora',
                'document' => '23.456.789/0001-56',
                'contact' => '(19) 3400-5005 / atendimento@sunlu.com.br',
            ],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            foreach ($suppliersData as $data) {
                $docData = $encrypt($data['document']);
                $contactData = $encrypt($data['contact']);

                Supplier::create([
                    'tenant_id' => $tenantId,
                    'name' => $data['name'],
                    'document_hash' => $docData['hash'],
                    'document_encrypted' => $docData['encrypted'],
                    'contact_encrypted' => $contactData['encrypted'],
                ]);
            }

            $this->command->info("  ✓ Tenant #{$tenantId}: " . count($suppliersData) . " fornecedores criados.");
        }

        $total = Supplier::count();
        $this->command->info("✓ Total: {$total} fornecedores criados com sucesso.");
    }
}