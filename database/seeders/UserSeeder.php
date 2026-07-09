<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\Tenant;
use App\Services\EncryptionService;
use App\Services\ImageOptimizationService;
use App\Services\UserService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $userService = app(UserService::class);

        $this->command->info('=== Criando usuários do sistema ===');

        $usersData = [
            [
                'first_name' => 'Admin',
                'last_name' => 'Master',
                'display_name' => 'Admin Master',
                'email' => 'admin@demanda3d.com',
                'access_level' => UserAccessLevel::ADMIN,
                'create_tenant' => true,
                'tenant_company' => 'Demanda3D Administradora',
            ],
            [
                'first_name' => 'Tech3D',
                'last_name' => 'Soluções',
                'display_name' => 'Tech3D Soluções Ltda',
                'email' => 'tech3d@demanda3d.com.br',
                'access_level' => UserAccessLevel::MANAGEMENT,
                'create_tenant' => true,
                'tenant_company' => 'Tech3D Soluções Ltda',
            ],
            [
                'first_name' => 'Maker',
                'last_name' => 'Lab',
                'display_name' => 'Maker Lab 3D',
                'email' => 'maker@demanda3d.com.br',
                'access_level' => UserAccessLevel::MANAGEMENT,
                'create_tenant' => true,
                'tenant_company' => 'Maker Lab 3D',
            ],
            [
                'first_name' => 'Prototype',
                'last_name' => 'Fast',
                'display_name' => 'Prototype Fast 3D',
                'email' => 'prototype@demanda3d.com.br',
                'access_level' => UserAccessLevel::MANAGEMENT,
                'create_tenant' => true,
                'tenant_company' => 'Prototype Fast 3D',
            ],
            [
                'first_name' => 'Cliente',
                'last_name' => 'Teste',
                'display_name' => 'Cliente Teste',
                'email' => 'cliente@demanda3d.com.br',
                'access_level' => UserAccessLevel::CUSTOMER,
                'create_tenant' => true,
                'tenant_company' => 'Cliente Teste',
            ],
        ];

        foreach ($usersData as $userData) {
            $user = $userService->create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
                'password' => Hash::make('Mudar@123'),
                'access_level' => $userData['access_level'],
                'data_nascimento' => $userData['data_nascimento'] ?? '1990-01-01',
            ]);

            if ($userData['create_tenant']) {
                $companyName = $userData['tenant_company'];
                $tenant = Tenant::create([
                    'user_id' => $user->id,
                    'company_name_encrypted' => EncryptionService::encryptWithHash($companyName)['encrypted'],
                    'company_name_hash' => EncryptionService::encryptWithHash($companyName)['hash'],
                    'fantasy_name_encrypted' => EncryptionService::encryptWithHash($companyName)['encrypted'],
                    'fantasy_name_hash' => EncryptionService::encryptWithHash($companyName)['hash'],
                    'document_encrypted' => EncryptionService::encryptWithHash('00.000.000/0001-00')['encrypted'],
                    'document_hash' => EncryptionService::encryptWithHash('00.000.000/0001-00')['hash'],
                    'phone_encrypted' => EncryptionService::encryptWithHash('(11) 99999-0000')['encrypted'],
                    'phone_hash' => EncryptionService::encryptWithHash('(11) 99999-0000')['hash'],
                    'address_encrypted' => EncryptionService::encryptWithHash('Av. Principal')['encrypted'],
                    'address_hash' => EncryptionService::encryptWithHash('Av. Principal')['hash'],
                    'number_encrypted' => EncryptionService::encryptWithHash('100')['encrypted'],
                    'number_hash' => EncryptionService::encryptWithHash('100')['hash'],
                    'district_encrypted' => EncryptionService::encryptWithHash('Centro')['encrypted'],
                    'district_hash' => EncryptionService::encryptWithHash('Centro')['hash'],
                    'city_encrypted' => EncryptionService::encryptWithHash('São Paulo')['encrypted'],
                    'city_hash' => EncryptionService::encryptWithHash('São Paulo')['hash'],
                    'state' => 'SP',
                    'zipcode' => '01000-000',
                    'active' => true,
                ]);
                $this->command->info("✓ Tenant criado para: {$userData['display_name']}");

                // Baixa e processa imagens de logo e banner para o tenant
                $this->downloadTenantImages($tenant, $userData['display_name']);
            }
        }

        $this->command->info('');
    }

    /**
     * Baixa imagens de logo e banner via picsum.photos e as aplica ao tenant
     * usando o pipeline de otimização (mesmo padrão do ProductSeeder).
     */
    private function downloadTenantImages(Tenant $tenant, string $displayName): void
    {
        $imageService = app(ImageOptimizationService::class);

        // Logo
        try {
            $logoUrl = "https://picsum.photos/seed/{$tenant->id}-logo/400/400";
            $this->command?->getOutput()->write("    ⏳ Baixando logo... ");
            $content = @file_get_contents($logoUrl);

            if ($content !== false) {
                $tmpPath = tempnam(sys_get_temp_dir(), 'seed_logo_') . '.jpg';
                file_put_contents($tmpPath, $content);

                $uploadedFile = new UploadedFile($tmpPath, 'logo.jpg', 'image/jpeg', null, true);

                $logoPath = $imageService->processTenantProfileUpload($uploadedFile, $tenant->id, 'logo');
                $tenant->update(['logo_path' => $logoPath]);

                $this->command?->getOutput()->writeln('<fg=green>✓ OK</>');
                @unlink($tmpPath);
            } else {
                $this->command?->getOutput()->writeln('<fg=red>✗ FALHA</>');
            }
        } catch (\Exception $e) {
            $this->command?->getOutput()->writeln("<fg=red>✗ ERRO logo: {$e->getMessage()}</>");
        }

        // Banner
        try {
            $bannerUrl = "https://picsum.photos/seed/{$tenant->id}-banner/1200/400";
            $this->command?->getOutput()->write("    ⏳ Baixando banner... ");
            $content = @file_get_contents($bannerUrl);

            if ($content !== false) {
                $tmpPath = tempnam(sys_get_temp_dir(), 'seed_banner_') . '.jpg';
                file_put_contents($tmpPath, $content);

                $uploadedFile = new UploadedFile($tmpPath, 'banner.jpg', 'image/jpeg', null, true);

                $bannerPath = $imageService->processTenantProfileUpload($uploadedFile, $tenant->id, 'banner');
                $tenant->update(['banner_path' => $bannerPath]);

                $this->command?->getOutput()->writeln('<fg=green>✓ OK</>');
                @unlink($tmpPath);
            } else {
                $this->command?->getOutput()->writeln('<fg=red>✗ FALHA</>');
            }
        } catch (\Exception $e) {
            $this->command?->getOutput()->writeln("<fg=red>✗ ERRO banner: {$e->getMessage()}</>");
        }
    }
}
