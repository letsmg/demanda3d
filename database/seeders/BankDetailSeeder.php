<?php

namespace Database\Seeders;

use App\Models\BankDetail;
use App\Models\Tenant;
use App\Services\EncryptionService;
use Illuminate\Database\Seeder;

class BankDetailSeeder extends Seeder
{
    public function run(): void
    {
        $sellerTenants = Tenant::where('active', true)
            ->whereHas('user', function ($q) {
                $q->whereIn('access_level', [
                    \App\Enums\UserAccessLevel::SELLER_1,
                    \App\Enums\UserAccessLevel::SELLER_2,
                ]);
            })
            ->get();

        if ($sellerTenants->isEmpty()) {
            $this->command?->warn('⚠ Nenhum tenant vendedor encontrado para dados bancários.');

            return;
        }

        $this->command?->info('=== Criando dados bancários dos vendedores ===');

        $encryptionService = app(EncryptionService::class);

        $banks = [
            ['name' => 'Banco do Brasil', 'routing' => '0001', 'account' => '100001-1'],
            ['name' => 'Itaú',            'routing' => '0347', 'account' => '200002-2'],
            ['name' => 'Bradesco',        'routing' => '0237', 'account' => '300003-3'],
            ['name' => 'Caixa Econômica',  'routing' => '0104', 'account' => '400004-4'],
        ];

        foreach ($sellerTenants as $index => $tenant) {
            $exists = BankDetail::where('tenant_id', $tenant->id)->exists();
            if ($exists) {
                continue;
            }

            $bank = $banks[$index % count($banks)];

            $routingData = $encryptionService->encryptWithHash($bank['routing']);
            $accountData = $encryptionService->encryptWithHash($bank['account']);
            $docData     = $encryptionService->encryptWithHash($tenant->document);

            BankDetail::create([
                'tenant_id'                  => $tenant->id,
                'bank_name'                  => $bank['name'],
                'routing_number_encrypted'   => $routingData['encrypted'],
                'account_number_encrypted'   => $accountData['encrypted'],
                'account_holder_name'        => $tenant->fantasy_name ?? $tenant->company_name ?? 'Vendedor',
                'account_holder_doc_encrypted' => $docData['encrypted'],
                'account_holder_doc_hash'      => $docData['hash'],
                'consented'                  => true,
                'consented_at'               => now(),
                'consent_ip'                 => '127.0.0.1',
                'consent_term_version'       => '1.0',
            ]);

            $this->command?->line("  ✓ Dados bancários: {$tenant->fantasy_name} → {$bank['name']}");
        }

        $this->command?->info('');
    }
}