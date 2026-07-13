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
                'access_level' => UserAccessLevel::SELLER_1,
                'create_tenant' => true,
                'tenant_company' => 'Tech3D Soluções Ltda',
            ],
            [
                'first_name' => 'Maker',
                'last_name' => 'Lab',
                'display_name' => 'Maker Lab 3D',
                'email' => 'maker@demanda3d.com.br',
                'access_level' => UserAccessLevel::SELLER_1,
                'create_tenant' => true,
                'tenant_company' => 'Maker Lab 3D',
            ],
            [
                'first_name' => 'Prototype',
                'last_name' => 'Fast',
                'display_name' => 'Prototype Fast 3D',
                'email' => 'prototype@demanda3d.com.br',
                'access_level' => UserAccessLevel::SELLER_1,
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
            [
                'first_name' => 'Operacional',
                'last_name' => 'Teste',
                'display_name' => 'Vendedor Operacional',
                'email' => 'seller2@demanda3d.com.br',
                'access_level' => UserAccessLevel::SELLER_2,
                'create_tenant' => true,
                'tenant_company' => 'Vendedor Operacional',
            ],
        ];

        foreach ($usersData as $userData) {
            $user = $userService->create([
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'display_name' => $userData['display_name'],
                'email' => $userData['email'],
                'password' => 'Mudar@123',
                'access_level' => $userData['access_level'],
                'birth_date' => $userData['birth_date'] ?? '1990-01-01',
                'email_verified_at' => now(),
            ]);

            if ($userData['create_tenant']) {
                $companyName = $userData['tenant_company'];
                $docData     = EncryptionService::encryptWithHash('00.000.000/0001-00');
                $legalData   = EncryptionService::encryptWithHash($companyName);
                $phoneData   = EncryptionService::encryptWithHash('(11) 99999-0000');
                $addressData = EncryptionService::encryptWithHash('Av. Principal, 100, Centro');

                $tenant = Tenant::create([
                    'user_id'              => $user->id,
                    'company_name_encrypted' => $legalData['encrypted'],
                    'company_name_hash'      => $legalData['hash'],
                    'fantasy_name'         => $companyName,
                    'fantasy_slug'         => \App\Models\Tenant::generateUniqueFantasySlug($companyName),
                    'document_type'        => 'cnpj',
                    'document_encrypted'   => $docData['encrypted'],
                    'document_hash'        => $docData['hash'],
                    'phone_encrypted'      => $phoneData['encrypted'],
                    'address_encrypted'    => $addressData['encrypted'],
                    'number'               => '100',
                    'district'             => 'Centro',
                    'city'                 => 'São Paulo',
                    'state'                => 'SP',
                    'zipcode'              => '01000-000',
                    'active'               => true,
                ]);

                $this->command->info("✓ Tenant criado para: {$userData['display_name']} (slug: {$tenant->fantasy_slug})");

                //$this->downloadTenantImages($tenant, $userData['display_name']);
            }
        }

        $this->command->info('');
    }

    // private function downloadTenantImages(Tenant $tenant, string $displayName): void
    // {
    //     $imageService = app(ImageOptimizationService::class);

    //     try {
    //         $logoUrl = "https://picsum.photos/seed/{$tenant->id}-logo/400/400";
    //         $this->command?->getOutput()->write("    ⏳ Baixando logo... ");
    //         $content = @file_get_contents($logoUrl);

    //         if ($content !== false) {
    //             $tmpPath = tempnam(sys_get_temp_dir(), 'seed_logo_') . '.jpg';
    //             file_put_contents($tmpPath, $content);

    //             $uploadedFile = new UploadedFile($tmpPath, 'logo.jpg', 'image/jpeg', null, true);
    //             $logoPath = $imageService->processTenantProfileUpload($uploadedFile, $tenant->id, 'logo');
    //             $tenant->update(['logo_path' => $logoPath]);

    //             $this->command?->getOutput()->writeln('<fg=green>✓ OK</>');
    //             @unlink($tmpPath);
    //         } else {
    //             $this->command?->getOutput()->writeln('<fg=red>✗ FALHA</>');
    //         }
    //     } catch (\Exception $e) {
    //         $this->command?->getOutput()->writeln("<fg=red>✗ ERRO logo: {$e->getMessage()}</>");
    //     }

    //     try {
    //         $bannerUrl = "https://picsum.photos/seed/{$tenant->id}-banner/1200/400";
    //         $this->command?->getOutput()->write("    ⏳ Baixando banner... ");
    //         $content = @file_get_contents($bannerUrl);

    //         if ($content !== false) {
    //             $tmpPath = tempnam(sys_get_temp_dir(), 'seed_banner_') . '.jpg';
    //             file_put_contents($tmpPath, $content);

    //             $uploadedFile = new UploadedFile($tmpPath, 'banner.jpg', 'image/jpeg', null, true);
    //             $bannerPath = $imageService->processTenantProfileUpload($uploadedFile, $tenant->id, 'banner');
    //             $tenant->update(['banner_path' => $bannerPath]);

    //             $this->command?->getOutput()->writeln('<fg=green>✓ OK</>');
    //             @unlink($tmpPath);
    //         } else {
    //             $this->command?->getOutput()->writeln('<fg=red>✗ FALHA</>');
    //         }
    //     } catch (\Exception $e) {
    //         $this->command?->getOutput()->writeln("<fg=red>✗ ERRO banner: {$e->getMessage()}</>");
    //     }
    // }
}
