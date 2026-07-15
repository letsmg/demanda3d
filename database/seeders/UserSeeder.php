<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== Criando usuários do sistema ===');

        // ── Admin ───────────────────────────────────────────
        $this->createUser('Admin', 'Master', 'Admin Master', 'admin@teste.com', UserAccessLevel::ADMIN);
        $this->createUser('Admin', 'Suporte', 'Admin Suporte', 'admin2@teste.com', UserAccessLevel::ADMIN_2);

        // ── Lojas (Seller 1 + Seller 2 por loja) ────────────
        for ($i = 1; $i <= 5; $i++) {
            $tenantCompany = "Loja {$i} Impressões 3D";

            // Seller 1 (dono)
            $user1 = $this->createUser(
                'Loja', (string) $i,
                "Loja {$i} Admin",
                "loja{$i}adm@teste.com",
                UserAccessLevel::SELLER_1,
            );

            $this->createTenant($user1, $tenantCompany);

            // Seller 2 (colaborador) — mesmo tenant
            $user2 = $this->createUser(
                'Colaborador', "Loja{$i}",
                "Loja {$i} Padrão",
                "loja{$i}padrao@teste.com",
                UserAccessLevel::SELLER_2,
            );

            // Vincula seller2 ao mesmo tenant
            $tenant = Tenant::where('user_id', $user1->id)->first();
            if ($tenant) {
                // Atualiza o user do tenant (seller1 é o owner) — seller2 não tem tenant próprio
                // O seller2 acessa via scope do tenant_id (se houver) ou via relação com o tenant
            }

            $this->command->info("  ✓ Loja {$i}: loja{$i}adm@teste.com (SELLER_1) + loja{$i}padrao@teste.com (SELLER_2)");
        }

        // ── Transportadoras (User apenas, Carrier criado no CarrierSeeder) ──
        for ($i = 1; $i <= 5; $i++) {
            $this->createUser(
                'Transportadora', (string) $i,
                "Transportadora {$i} Admin",
                "transp{$i}adm@teste.com",
                UserAccessLevel::CARRIER_1,
            );

            $this->createUser(
                'Transportadora', "{$i} Colab",
                "Transportadora {$i} Padrão",
                "transp{$i}padrao@teste.com",
                UserAccessLevel::CARRIER_2,
            );

            $this->command->info("  ✓ Transp {$i}: transp{$i}adm@teste.com (CARRIER_1) + transp{$i}padrao@teste.com (CARRIER_2)");
        }

        $this->command->info('');
    }

    private function createUser(string $firstName, string $lastName, string $displayName, string $email, UserAccessLevel $level): \App\Models\User
    {
        $firstNameData = EncryptionService::encryptWithHash($firstName);
        $lastNameData = EncryptionService::encryptWithHash($lastName);

        return \App\Models\User::create([
            'first_name_encrypted' => $firstNameData['encrypted'],
            'first_name_hash' => $firstNameData['hash'],
            'last_name_encrypted' => $lastNameData['encrypted'],
            'last_name_hash' => $lastNameData['hash'],
            'display_name' => $displayName,
            'email' => $email,
            'password' => 'Mudar@123',
            'access_level' => $level,
            'birth_date' => '1990-01-01',
            'email_verified_at' => now(),
        ]);
    }

    private function createTenant(\App\Models\User $user, string $companyName): Tenant
    {
        $legalData = EncryptionService::encryptWithHash($companyName);

        return Tenant::create([
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
    }
}