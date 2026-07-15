<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Carrier;
use App\Models\CarrierCoverageRange;
use App\Models\CarrierTenantAgreement;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all();

        $carriersData = [
            [
                'fantasy_name'   => 'Transportadora Rápida',
                'company_name'   => 'Transportadora Rápida Ltda',
                'email'          => 'transp1@teste.com',
                'password'       => 'Mudar@123',
                'document_type'  => 'cnpj',
                'document'       => '11222333000144',
                'phone'          => '1131234567',
                'address'        => 'Avenida Paulista, 1000 — Bela Vista, São Paulo, SP — 01310-000',
                'website_url'    => 'https://transp1.com.br',
                'access_level'   => UserAccessLevel::CARRIER_1,
                'coverage'       => [
                    ['title' => 'Grande São Paulo', 'cep_start' => '01000000', 'cep_end' => '09999999'],
                    ['title' => 'Interior SP',     'cep_start' => '10000000', 'cep_end' => '19999999'],
                    ['title' => 'Rio de Janeiro',   'cep_start' => '20000000', 'cep_end' => '28999999'],
                    ['title' => 'Minas Gerais',     'cep_start' => '30000000', 'cep_end' => '39999999'],
                ],
            ],
            [
                'fantasy_name'   => 'Transportadora Veloz',
                'company_name'   => 'Transportadora Veloz Ltda',
                'email'          => 'transp2@teste.com',
                'password'       => 'Mudar@123',
                'document_type'  => 'cnpj',
                'document'       => '22333444000155',
                'phone'          => '2131234567',
                'address'        => 'Avenida Rio Branco, 200 — Centro, Rio de Janeiro, RJ — 20040-000',
                'website_url'    => 'https://transp2.express',
                'access_level'   => UserAccessLevel::CARRIER_2,
                'coverage'       => [
                    ['title' => 'Capital RJ',     'cep_start' => '20000000', 'cep_end' => '23799999'],
                    ['title' => 'Interior RJ',    'cep_start' => '23800000', 'cep_end' => '28999999'],
                    ['title' => 'Grande São Paulo','cep_start' => '01000000', 'cep_end' => '09999999'],
                ],
            ],
        ];

        // Busca os users já criados pelo UserSeeder
        $users = \App\Models\User::whereIn('access_level', UserAccessLevel::carrierValues())->get()->keyBy('email');

        foreach ($carriersData as $data) {
            $user = $users[$data['email']] ?? null;

            if (! $user) {
                $this->command->warn("  ⚠ User {$data['email']} não encontrado — pulando carrier");
                continue;
            }

            $docData = EncryptionService::encryptWithHash($data['document']);
            $companyData = EncryptionService::encryptWithHash($data['company_name']);
            $phoneData = EncryptionService::encryptWithHash($data['phone']);
            $addressData = EncryptionService::encryptWithHash($data['address']);

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
                'is_profile_complete'   => true,
            ]);

            // Cobertura
            foreach ($data['coverage'] as $range) {
                CarrierCoverageRange::create([
                    'carrier_id' => $carrier->id,
                    'title'      => $range['title'],
                    'cep_start'  => $range['cep_start'],
                    'cep_end'    => $range['cep_end'],
                ]);
            }

            // Acordos ativos com todos os tenants
            foreach ($tenants as $tenant) {
                CarrierTenantAgreement::create([
                    'tenant_id'  => $tenant->id,
                    'carrier_id' => $carrier->id,
                    'status'     => CarrierTenantAgreement::STATUS_ACTIVE,
                ]);
            }

            $this->command->info("  ✓ Carrier: {$data['fantasy_name']} ({$data['email']})");
        }

        $this->command->info('✓ 2 transportadoras criadas (transp1..2@teste.com / Mudar@123)');
    }
}