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
            if ($this->command) {
                $this->command->warn('Nenhum tenant encontrado. Execute UserSeeder primeiro.');
            }
            return;
        }

        if ($this->command) {
            $this->command->info('=== Criando fornecedores ===');
        }

        $suppliersData = [
            [
                'name' => '3DLab Filamentos Ltda',
                'doc_type' => 'CNPJ',
                'document' => '12.345.678/0001-90',
                'ie' => '123456789',
                'contact' => 'Vendas - 3dlab',
                'email' => 'vendas@3dlab.com.br',
                'website' => 'https://www.3dlab.com.br',
                'phone1' => '(11) 4000-1001',
                'phone2' => '(11) 4000-1002',
                'contact1' => 'Carlos Silva',
                'contact2' => 'Financeiro',
                'address' => 'Rua dos Pinheiros',
                'number' => '500',
                'district' => 'Jardins',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zipcode' => '01415-000',
                'notes' => 'Fornecedor premium de filamentos PLA e ABS. Entrega em 24h na capital.',
            ],
            [
                'name' => 'eSun Brasil Importação',
                'doc_type' => 'CNPJ',
                'document' => '98.765.432/0001-10',
                'ie' => '987654321',
                'contact' => 'Comercial - eSun',
                'email' => 'contato@esunbrasil.com',
                'website' => 'https://www.esunbrasil.com',
                'phone1' => '(21) 3500-2002',
                'phone2' => null,
                'contact1' => 'Ana Oliveira',
                'contact2' => null,
                'address' => 'Av. Rio Branco',
                'number' => '1200',
                'district' => 'Centro',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'zipcode' => '20040-002',
                'notes' => 'Importadora oficial eSun. Preços competitivos para pedidos acima de R$5.000.',
            ],
            [
                'name' => 'Creality Supply Chain',
                'doc_type' => 'CNPJ',
                'document' => '45.678.901/0001-23',
                'ie' => '456789012',
                'contact' => 'Atendimento - Creality',
                'email' => 'pedidos@crealitysupply.com.br',
                'website' => 'https://www.crealitysupply.com.br',
                'phone1' => '(48) 3200-3003',
                'phone2' => '(48) 3200-3004',
                'contact1' => 'Pedro Costa',
                'contact2' => 'Logística',
                'address' => 'Rua XV de Novembro',
                'number' => '800',
                'district' => 'Centro',
                'city' => 'Florianópolis',
                'state' => 'SC',
                'zipcode' => '88015-200',
                'notes' => 'Distribuidor autorizado Creality. Peças de reposição e impressoras 3D.',
            ],
            [
                'name' => 'Polymaker Indústria Química',
                'doc_type' => 'CNPJ',
                'document' => '67.890.123/0001-45',
                'ie' => '678901234',
                'contact' => 'Vendas - Polymaker',
                'email' => 'vendas@polymaker.ind.br',
                'website' => 'https://www.polymaker.ind.br',
                'phone1' => '(31) 3100-4004',
                'phone2' => null,
                'contact1' => 'Maria Santos',
                'contact2' => 'Suporte Técnico',
                'address' => 'Av. Afonso Pena',
                'number' => '3000',
                'district' => 'Funcionários',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'zipcode' => '30130-008',
                'notes' => 'Fabricante nacional de filamentos especiais. Atendimento técnico dedicado.',
            ],
            [
                'name' => 'SUNLU Distribuidora',
                'doc_type' => 'CNPJ',
                'document' => '23.456.789/0001-56',
                'ie' => '234567890',
                'contact' => 'Comercial - SUNLU',
                'email' => 'atendimento@sunlu.com.br',
                'website' => 'https://www.sunlu.com.br',
                'phone1' => '(19) 3400-5005',
                'phone2' => '(19) 3400-5006',
                'contact1' => 'João Silva',
                'contact2' => null,
                'address' => 'Rua Barão de Itapura',
                'number' => '1500',
                'district' => 'Botafogo',
                'city' => 'Campinas',
                'state' => 'SP',
                'zipcode' => '13020-430',
                'notes' => 'Distribuidor SUNLU com maior variedade de cores PETG do mercado.',
            ],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;

            foreach ($suppliersData as $data) {
                $docData = $encrypt($data['document']);
                $contactData = $encrypt($data['contact']);
                $addressData = $encrypt($data['address']);
                $numberData = $encrypt($data['number']);
                $districtData = $encrypt($data['district']);
                $cityData = $encrypt($data['city']);

                // Campos opcionais — só criptografa se não forem null
                $contact1Data = !empty($data['contact1']) ? $encrypt($data['contact1']) : null;
                $contact2Data = !empty($data['contact2']) ? $encrypt($data['contact2']) : null;
                $phone1Data = !empty($data['phone1']) ? $encrypt($data['phone1']) : null;
                $phone2Data = !empty($data['phone2']) ? $encrypt($data['phone2']) : null;

                Supplier::withoutGlobalScopes()->firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'document_hash' => $docData['hash'],
                    ],
                    [
                        'name' => $data['name'],
                        'doc_type' => $data['doc_type'],
                        'ie' => $data['ie'] ?? null,
                        'document_encrypted' => $docData['encrypted'],
                        'contact_encrypted' => $contactData['encrypted'],
                        'email' => $data['email'] ?? null,
                        'website' => $data['website'] ?? null,
                        'phone1_encrypted' => $phone1Data['encrypted'] ?? null,
                        'phone1_hash' => $phone1Data['hash'] ?? null,
                        'phone2_encrypted' => $phone2Data['encrypted'] ?? null,
                        'phone2_hash' => $phone2Data['hash'] ?? null,
                        'contact1_encrypted' => $contact1Data['encrypted'] ?? null,
                        'contact1_hash' => $contact1Data['hash'] ?? null,
                        'contact2_encrypted' => $contact2Data['encrypted'] ?? null,
                        'contact2_hash' => $contact2Data['hash'] ?? null,
                        'address_encrypted' => $addressData['encrypted'],
                        'address_hash' => $addressData['hash'],
                        'number_encrypted' => $numberData['encrypted'],
                        'number_hash' => $numberData['hash'],
                        'district_encrypted' => $districtData['encrypted'],
                        'district_hash' => $districtData['hash'],
                        'city_encrypted' => $cityData['encrypted'],
                        'city_hash' => $cityData['hash'],
                        'state' => $data['state'] ?? null,
                        'zipcode' => $data['zipcode'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'is_active' => true,
                    ],
                );
            }

            if ($this->command) {
                $this->command->info("  ✓ Tenant #{$tenantId}: " . count($suppliersData) . " fornecedores criados.");
            }
        }

        $total = Supplier::count();
        if ($this->command) {
            $this->command->info("✓ Total: {$total} fornecedores criados com sucesso.");
        }
    }
}