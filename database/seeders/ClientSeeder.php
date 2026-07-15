<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $makeEncr = fn ($value) => EncryptionService::encryptWithHash($value);
        $password = Hash::make('Mudar@123');

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('Nenhum tenant encontrado para associar clientes.');
            return;
        }

        $firstTenant = $tenants->first();

        // Clientes 1 a 5 — dados padronizados
        $clientsData = [
            [
                'email' => 'cliente1@teste.com',
                'display_name' => 'Cliente 1 Silva',
                'first_name' => 'João',
                'last_name' => 'Silva',
                'doc' => '12345678901',
                'phone1' => '(11) 99999-0001',
                'address' => 'Rua das Flores',
                'number' => '10',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zipcode' => '01000-001',
            ],
            [
                'email' => 'cliente2@teste.com',
                'display_name' => 'Cliente 2 Santos',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'doc' => '23456789012',
                'phone1' => '(11) 99999-0002',
                'address' => 'Av. Paulista',
                'number' => '100',
                'city' => 'São Paulo',
                'state' => 'SP',
                'zipcode' => '01310-000',
            ],
            [
                'email' => 'cliente3@teste.com',
                'display_name' => 'Cliente 3 Oliveira',
                'first_name' => 'Pedro',
                'last_name' => 'Oliveira',
                'doc' => '34567890123',
                'phone1' => '(21) 99999-0003',
                'address' => 'Rua do Ouvidor',
                'number' => '50',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'zipcode' => '20040-000',
            ],
            [
                'email' => 'cliente4@teste.com',
                'display_name' => 'Cliente 4 Costa',
                'first_name' => 'Ana',
                'last_name' => 'Costa',
                'doc' => '45678901234',
                'phone1' => '(31) 99999-0004',
                'address' => 'Av. Afonso Pena',
                'number' => '800',
                'city' => 'Belo Horizonte',
                'state' => 'MG',
                'zipcode' => '30130-000',
            ],
            [
                'email' => 'cliente5@teste.com',
                'display_name' => 'Cliente 5 Lima',
                'first_name' => 'Carlos',
                'last_name' => 'Lima',
                'doc' => '56789012345',
                'phone1' => '(48) 99999-0005',
                'address' => 'Rua Felipe Schmidt',
                'number' => '200',
                'city' => 'Florianópolis',
                'state' => 'SC',
                'zipcode' => '88010-000',
            ],
        ];

        foreach ($clientsData as $data) {
            $firstNameData = EncryptionService::encryptWithHash($data['first_name']);
            $lastNameData = EncryptionService::encryptWithHash($data['last_name']);
            $docData = EncryptionService::encryptWithHash($data['doc']);
            $addressData = EncryptionService::encryptWithHash($data['address']);
            $numberData = EncryptionService::encryptWithHash($data['number']);
            $stateData = EncryptionService::encryptWithHash($data['state']);
            $zipcodeData = EncryptionService::encryptWithHash($data['zipcode']);
            $cityData = EncryptionService::encryptWithHash($data['city']);
            $phone1Data = EncryptionService::encryptWithHash($data['phone1']);

            Client::updateOrCreate(
                ['email' => $data['email']],
                [
                    'tenant_id' => $firstTenant->id,
                    'password' => $password,
                    'display_name' => $data['display_name'],
                    'doc_type' => 'CPF',
                    'first_name_encrypted' => $firstNameData['encrypted'],
                    'first_name_hash' => $firstNameData['hash'],
                    'last_name_encrypted' => $lastNameData['encrypted'],
                    'last_name_hash' => $lastNameData['hash'],
                    'doc_encrypted' => $docData['encrypted'],
                    'doc_hash' => $docData['hash'],
                    'address_encrypted' => $addressData['encrypted'],
                    'address_hash' => $addressData['hash'],
                    'number_encrypted' => $numberData['encrypted'],
                    'number_hash' => $numberData['hash'],
                    'state_encrypted' => $stateData['encrypted'],
                    'state_hash' => $stateData['hash'],
                    'zipcode_encrypted' => $zipcodeData['encrypted'],
                    'zipcode_hash' => $zipcodeData['hash'],
                    'city_encrypted' => $cityData['encrypted'],
                    'city_hash' => $cityData['hash'],
                    'phone1_encrypted' => $phone1Data['encrypted'],
                    'phone1_hash' => $phone1Data['hash'],
                    'is_profile_complete' => true,
                ]
            );
        }

        $this->command->info('✓ 5 clientes atualizados/criados (cliente1..5@teste.com / Mudar@123)');
    }
}