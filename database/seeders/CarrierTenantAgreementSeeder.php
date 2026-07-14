<?php

namespace Database\Seeders;

use App\Models\Carrier;
use App\Models\CarrierTenantAgreement;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CarrierTenantAgreementSeeder extends Seeder
{
    public function run(): void
    {
        // Busca tenants de vendedores ativos (não clientes)
        $sellerTenants = Tenant::where('active', true)
            ->whereHas('user', function ($q) {
                $q->whereIn('access_level', [\App\Enums\UserAccessLevel::SELLER_1, \App\Enums\UserAccessLevel::SELLER_2]);
            })
            ->get();

        if ($sellerTenants->isEmpty()) {
            $this->command?->warn('⚠ Nenhum tenant vendedor encontrado.');

            return;
        }

        $carriers = Carrier::where('is_active', true)->get();

        if ($carriers->isEmpty()) {
            $this->command?->warn('⚠ Nenhuma transportadora ativa encontrada.');

            return;
        }

        $this->command?->info('=== Criando contratos entre vendedores e transportadoras ===');

        // Para cada vendedor, vincula 1-2 transportadoras aleatórias
        foreach ($sellerTenants as $tenant) {
            $randomCarriers = $carriers->random(min(rand(1, 2), $carriers->count()));

            foreach ($randomCarriers as $carrier) {
                $exists = CarrierTenantAgreement::where('tenant_id', $tenant->id)
                    ->where('carrier_id', $carrier->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                CarrierTenantAgreement::create([
                    'tenant_id'  => $tenant->id,
                    'carrier_id' => $carrier->id,
                    'status'     => CarrierTenantAgreement::STATUS_ACTIVE,
                ]);

                $this->command?->line("  ✓ Contrato: {$tenant->fantasy_name} ↔ {$carrier->fantasy_name}");
            }
        }

        $this->command?->info('');
    }
}