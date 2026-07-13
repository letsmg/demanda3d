<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Carrier;
use App\Models\CarrierCoverageRange;
use App\Models\CarrierTenantAgreement;
use App\Models\Tenant;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $carriersData = [
            [
                'fantasy_name'   => 'Transportadora Rapidez Ltda',
                'company_name'   => 'Rapidez Transportes e Logística Ltda',
                'email'          => 'transportadora@demanda3d.com',
                'password'       => 'Mudar@123',
                'document_type'  => 'cnpj',
                'document'       => '11222333000144',
                'phone'          => '1131234567',
                'address'        => 'Avenida Paulista, 1000 — Bela Vista, São Paulo, SP — 01310-000',
                'website_url'    => 'https://rapidez.com.br',
                'coverage'       => [
                    ['title' => 'Grande São Paulo', 'cep_start' => '01000000', 'cep_end' => '09999999'],
                    ['title' => 'Interior SP',     'cep_start' => '10000000', 'cep_end' => '19999999'],
                    ['title' => 'Rio de Janeiro',   'cep_start' => '20000000', 'cep_end' => '28999999'],
                    ['title' => 'Minas Gerais',     'cep_start' => '30000000', 'cep_end' => '39999999'],
                ],
            ],
            [
                'fantasy_name'   => 'Entregas Cariocas Express',
                'company_name'   => 'Cariocas Express Logística Ltda',
                'email'          => 'cariocas@express.com.br',
                'password'       => 'Mudar@123',
                'document_type'  => 'cnpj',
                'document'       => '22333444000155',
                'phone'          => '2131234567',
                'address'        => 'Avenida Rio Branco, 200 — Centro, Rio de Janeiro, RJ — 20040-000',
                'website_url'    => 'https://cariocas.express',
                'coverage'       => [
                    ['title' => 'Capital RJ',     'cep_start' => '20000000', 'cep_end' => '23799999'],
                    ['title' => 'Interior RJ',    'cep_start' => '23800000', 'cep_end' => '28999999'],
                    ['title' => 'Grande São Paulo','cep_start' => '01000000', 'cep_end' => '09999999'],
                ],
            ],
            [
                'fantasy_name'   => 'Logística Mineira S.A.',
                'company_name'   => 'Logística Mineira S.A.',
                'email'          => 'logistica@mineira.com.br',
                'password'       => 'Mudar@123',
                'document_type'  => 'cnpj',
                'document'       => '33444555000166',
                'phone'          => '3131234567',
                'address'        => 'Rua da Bahia, 500 — Centro, Belo Horizonte, MG — 30130-000',
                'website_url'    => 'https://mineira.log.br',
                'coverage'       => [
                    ['title' => 'Belo Horizonte e Região', 'cep_start' => '30000000', 'cep_end' => '35999999'],
                    ['title' => 'Interior MG',             'cep_start' => '36000000', 'cep_end' => '39999999'],
                ],
            ],
            [
                'fantasy_name'   => 'Frete Fácil S.A.',
                'company_name'   => 'Frete Fácil Logística Integrada S.A.',
                'email'          => 'fretefacil@demanda3d.com',
                'password'       => 'Mudar@123',
                'document_type'  => 'cnpj',
                'document'       => '44555666000177',
                'phone'          => '1143215678',
                'address'        => 'Rua Augusta, 1500 — Consolação, São Paulo, SP — 01310-000',
                'website_url'    => 'https://fretefacil.com.br',
                'access_level'   => UserAccessLevel::CARRIER_2,
                'coverage'       => [
                    ['title' => 'Grande São Paulo', 'cep_start' => '01000000', 'cep_end' => '09999999'],
                ],
            ],
        ];

        foreach ($carriersData as $i => $data) {
            try {
                // Paridade LGPD para User (first_name/last_name)
                $nameParts     = explode(' ', $data['fantasy_name'], 2);
                $firstName     = $nameParts[0];
                $lastName      = $nameParts[1] ?? 'Transportes';
                $firstNameData = EncryptionService::encryptWithHash($firstName);
                $lastNameData  = EncryptionService::encryptWithHash($lastName);

                // Paridade LGPD para Carrier (company_name, document, phone, address)
                $docData     = EncryptionService::encryptWithHash($data['document']);
                $companyData = EncryptionService::encryptWithHash($data['company_name']);
                $phoneData   = EncryptionService::encryptWithHash($data['phone']);
                $addressData = EncryptionService::encryptWithHash($data['address']);

                // 1. Cria User global (o cast 'hashed' cuida da senha)
                $user = User::create([
                    'email'                => $data['email'],
                    'display_name'         => $data['fantasy_name'],
                    'password'             => $data['password'],
                    'access_level'         => $data['access_level'] ?? UserAccessLevel::CARRIER_1,
                    'email_verified_at'    => now(),
                    'first_name_encrypted' => $firstNameData['encrypted'],
                    'first_name_hash'      => $firstNameData['hash'],
                    'last_name_encrypted'  => $lastNameData['encrypted'],
                    'last_name_hash'       => $lastNameData['hash'],
                ]);

                $this->command->info("  ✓ User criado: {$user->email} (id={$user->id}, level={$user->access_level->value})");
            } catch (\Throwable $e) {
                $this->command->error("  ✗ ERRO no carrier #{$i} ({$data['email']}): " . $e->getMessage());
                $this->command->error("    Trace: " . $e->getTraceAsString());
                throw $e; // Interrompe o seed para diagnóstico
            }

            // 2. Cria perfil Carrier
            $carrier = Carrier::create([
                'user_id'               => $user->id,
                'fantasy_name'          => $data['fantasy_name'],
                'slug'                  => Carrier::generateUniqueSlug($data['fantasy_name']),
                'company_name_encrypted'=> $companyData['encrypted'],
                'company_name_hash'     => $companyData['hash'],
                'document_type'         => $data['document_type'],
                'document_encrypted'    => $docData['encrypted'],
                'document_hash'         => $docData['hash'],
                'address_encrypted'     => $addressData['encrypted'],
                'phone_encrypted'       => $phoneData['encrypted'],
                'website_url'           => $data['website_url'] ?? null,
                'is_active'             => true,
            ]);

            // 3. Cria faixas de cobertura
            foreach ($data['coverage'] as $range) {
                CarrierCoverageRange::create([
                    'carrier_id' => $carrier->id,
                    'title'      => $range['title'],
                    'cep_start'  => $range['cep_start'],
                    'cep_end'    => $range['cep_end'],
                ]);
            }

            // 4. Acordos ativos com todos os tenants
            foreach ($tenants as $tenant) {
                CarrierTenantAgreement::create([
                    'tenant_id'  => $tenant->id,
                    'carrier_id' => $carrier->id,
                    'status'     => CarrierTenantAgreement::STATUS_ACTIVE,
                ]);
            }
        }

        $this->command->newLine();
        $this->command->info('═══ CREDENCIAIS DE LOGIN — TRANSPORTADORAS ═══');
        $this->command->table(
            ['E-mail', 'Senha', 'Transportadora'],
            collect($carriersData)->map(fn ($d) => [$d['email'], $d['password'], $d['fantasy_name']])->toArray()
        );
        $this->command->info('');
        $this->command->info('   👉 Use: transportadora@demanda3d.com / Mudar@123');
    }
}