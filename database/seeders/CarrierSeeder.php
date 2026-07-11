<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\State;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CarrierSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            $tenant = Tenant::create([
                'user_id' => 1,
                'state'   => 'SP',
                'zipcode' => '01310-000',
                'active'  => true,
            ]);
        }

        $sp = State::where('uf', 'SP')->where('cep_start', '01000-000')->first();
        $rj = State::where('uf', 'RJ')->first();
        $mg = State::where('uf', 'MG')->first();

        $carriers = [
            [
                'name'            => 'Transportadora Rapidez Ltda',
                'email'           => 'contato@rapidez.com.br',
                'password'        => 'password',
                'doc_type'        => 'CNPJ',
                'document'        => '11222333000144',
                'ie'              => '123456789',
                'state'           => 'SP',
                'zipcode'         => '01310-000',
                'city'            => 'São Paulo',
                'address'         => 'Avenida Paulista',
                'number'          => '1000',
                'district'        => 'Bela Vista',
                'phone1'          => '1131234567',
                'contact1'        => 'Carlos Gestor',
                'is_active'       => true,
                'state_ids'       => [$sp->id, $rj->id, $mg->id],
            ],
            [
                'name'            => 'Entregas Cariocas Express',
                'email'           => 'cariocas@express.com.br',
                'password'        => 'password',
                'doc_type'        => 'CNPJ',
                'document'        => '22333444000155',
                'ie'              => '987654321',
                'state'           => 'RJ',
                'zipcode'         => '20040-000',
                'city'            => 'Rio de Janeiro',
                'address'         => 'Avenida Rio Branco',
                'number'          => '200',
                'district'        => 'Centro',
                'phone1'          => '2131234567',
                'contact1'        => 'Ana Gestora',
                'is_active'       => true,
                'state_ids'       => [$rj->id, $sp->id],
            ],
            [
                'name'            => 'Logística Mineira S.A.',
                'email'           => 'logistica@mineira.com.br',
                'password'        => 'password',
                'doc_type'        => 'CNPJ',
                'document'        => '33444555000166',
                'ie'              => '555666777',
                'state'           => 'MG',
                'zipcode'         => '30130-000',
                'city'            => 'Belo Horizonte',
                'address'         => 'Rua da Bahia',
                'number'          => '500',
                'district'        => 'Centro',
                'phone1'          => '3131234567',
                'contact1'        => 'Pedro Logística',
                'is_active'       => true,
                'state_ids'       => [$mg->id],
            ],
        ];

        foreach ($carriers as $data) {
            $docData = EncryptionService::encryptWithHash($data['document']);
            $addrData = EncryptionService::encryptWithHash($data['address']);
            $numData = EncryptionService::encryptWithHash($data['number']);
            $districtData = EncryptionService::encryptWithHash($data['district']);
            $cityData = EncryptionService::encryptWithHash($data['city']);
            $phone1Data = EncryptionService::encryptWithHash($data['phone1']);
            $contact1Data = EncryptionService::encryptWithHash($data['contact1']);

            $carrier = Carrier::create([
                'tenant_id'          => $tenant->id,
                'name'               => $data['name'],
                'email'              => $data['email'],
                'password'           => Hash::make($data['password']),
                'doc_type'           => $data['doc_type'],
                'document_encrypted' => $docData['encrypted'],
                'document_hash'      => $docData['hash'],
                'ie'                 => $data['ie'] ?? null,
                'state'              => $data['state'],
                'zipcode'            => $data['zipcode'],
                'address_encrypted'  => $addrData['encrypted'],
                'address_hash'       => $addrData['hash'],
                'number_encrypted'   => $numData['encrypted'],
                'number_hash'        => $numData['hash'],
                'district_encrypted' => $districtData['encrypted'],
                'district_hash'      => $districtData['hash'],
                'city_encrypted'     => $cityData['encrypted'],
                'city_hash'          => $cityData['hash'],
                'phone1_encrypted'   => $phone1Data['encrypted'],
                'phone1_hash'        => $phone1Data['hash'],
                'contact1_encrypted' => $contact1Data['encrypted'],
                'contact1_hash'      => $contact1Data['hash'],
                'is_active'          => $data['is_active'],
            ]);

            // Vincula estados de atuação
            if (! empty($data['state_ids'])) {
                $carrier->states()->sync($data['state_ids']);
            }
        }

        // Vincula transportadoras aos vendedores (usuários staff que possuem tenant)
        $staffUsers = \App\Models\User::whereIn('access_level', [0, 1, 10])->has('tenant')->get();
        $allCarriers = Carrier::all();

        foreach ($staffUsers as $user) {
            foreach ($allCarriers as $carrier) {
                \App\Models\VendorCarrier::firstOrCreate(
                    ['user_id' => $user->id, 'carrier_id' => $carrier->id],
                    ['status' => 'approved', 'responded_at' => now()],
                );
            }
        }

        // ─────────────────────────────────────────────
        // Credenciais de teste para login como transportador
        // ─────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('═══ TRANSPORTADORAS (CARRIERS) ═══');
        $this->command->table(
            ['E-mail', 'Senha', 'Nome'],
            [
                ['contato@rapidez.com.br', 'password', 'Transportadora Rapidez Ltda'],
                ['cariocas@express.com.br', 'password', 'Entregas Cariocas Express'],
                ['logistica@mineira.com.br', 'password', 'Logística Mineira S.A.'],
            ]
        );
    }
}
