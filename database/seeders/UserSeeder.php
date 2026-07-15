<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Criando usuários do sistema ===');

        $usersData = [
            [
                'first_name' => 'Admin',
                'last_name' => 'Master',
                'display_name' => 'Admin Master',
                'email' => 'admin@teste.com',
                'access_level' => UserAccessLevel::ADMIN,
                'create_tenant' => false,
                'tenant_company' => null,
            ],
            [
                'first_name' => 'Loja',
                'last_name' => 'Um',
                'display_name' => 'Loja 1',
                'email' => 'loja1@teste.com',
                'access_level' => UserAccessLevel::SELLER_1,
                'create_tenant' => true,
                'tenant_company' => 'Loja 1 Impressões 3D',
            ],
            [
                'first_name' => 'Loja',
                'last_name' => 'Dois',
                'display_name' => 'Loja 2',
                'email' => 'loja2@teste.com',
                'access_level' => UserAccessLevel::SELLER_2,
                'create_tenant' => true,
                'tenant_company' => 'Loja 2 Modelagem 3D',
            ],
        ];

        foreach ($usersData as $userData) {
            $firstNameData = EncryptionService::encryptWithHash($userData['first_name']);
            $lastNameData = EncryptionService::encryptWithHash($userData['last_name']);

            $user = \App\Models\User::create([
                'first_name_encrypted' => $firstNameData['encrypted'],
                'first_name_hash' => $firstNameData['hash'],
                'last_name_encrypted' => $lastNameData['encrypted'],
                'last_name_hash' => $lastNameData['hash'],
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
                'password' => 'Mudar@123',
                'access_level' => $userData['access_level'],
                'birth_date' => '1990-01-01',
                'email_verified_at' => now(),
            ]);

            $this->command->info("  ✓ User: {$userData['email']} (level={$userData['access_level']->value})");

            if ($userData['create_tenant']) {
                $companyName = $userData['tenant_company'];
                $legalData = EncryptionService::encryptWithHash($companyName);

                $tenant = Tenant::create([
                    'user_id' => $user->id,
                    'company_name_encrypted' => $legalData['encrypted'],
                    'company_name_hash' => $legalData['hash'],
                    'fantasy_name' => $companyName,
                    'fantasy_slug' => Tenant::generateUniqueFantasySlug($companyName),
                    'document_type' => 'cnpj',
                    'document' => '00.000.000/0001-00',
                    'phone' => '(11) 99999-0000',
                    'address' => 'Av. Principal, 100, Centro',
                    'number' => '100',
                    'district' => 'Centro',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zipcode' => '01000-000',
                    'active' => true,
                    'is_profile_complete' => true,
                ]);

                $this->command->info("    ✓ Tenant: {$tenant->fantasy_slug} (perfil completo)");
            }
        }

        // ── Transportadoras (Carrier) ──────────────────────
        $carriersData = [
            [
                'fantasy_name' => 'Transportadora Rápida',
                'company_name' => 'Transportadora Rápida Ltda',
                'email' => 'transp1@teste.com',
                'access_level' => UserAccessLevel::CARRIER_1,
            ],
            [
                'fantasy_name' => 'Transportadora Veloz',
                'company_name' => 'Transportadora Veloz Ltda',
                'email' => 'transp2@teste.com',
                'access_level' => UserAccessLevel::CARRIER_2,
            ],
        ];

        foreach ($carriersData as $data) {
            $nameParts = explode(' ', $data['fantasy_name'], 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Transportes';
            $firstNameData = EncryptionService::encryptWithHash($firstName);
            $lastNameData = EncryptionService::encryptWithHash($lastName);

            \App\Models\User::create([
                'first_name_encrypted' => $firstNameData['encrypted'],
                'first_name_hash' => $firstNameData['hash'],
                'last_name_encrypted' => $lastNameData['encrypted'],
                'last_name_hash' => $lastNameData['hash'],
                'display_name' => $data['company_name'],
                'email' => $data['email'],
                'password' => 'Mudar@123',
                'access_level' => $data['access_level'],
                'birth_date' => '1990-01-01',
                'email_verified_at' => now(),
            ]);

            $this->command->info("  ✓ User: {$data['email']} (level={$data['access_level']->value})");
        }

        $this->command->info('');
    }
}