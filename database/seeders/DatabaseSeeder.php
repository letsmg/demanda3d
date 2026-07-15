<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Ordem: Users → LegalDocuments → Categories → SeoSettings → Suppliers → Clients → Products → Inputs → Orders → Threads → Messages → Disputes
     */
    public function run(): void
    {
        // Limpa sessões do Redis (migrate:fresh apaga o banco mas não o cache de sessão)
        if (config('session.driver') === 'redis') {
            try {
                \Illuminate\Support\Facades\Redis::command('flushdb');
                $this->command->info('⚡ Redis limpo (sessões invalidadas).');
            } catch (\Exception $e) {
                $this->command->warn('⚠ Não foi possível limpar o Redis: '.$e->getMessage());
            }
        }

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║        DEMANDA3D — SEED INICIADO          ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->info('');

        $this->call([
            UserSeeder::class,
            LegalDocumentSeeder::class,
            StateSeeder::class,
            CategorySeeder::class,
            SeoSettingSeeder::class,
            SupplierSeeder::class,
            ClientSeeder::class,
            CarrierSeeder::class,
            CarrierTenantAgreementSeeder::class,
            BankDetailSeeder::class,
            ProductSeeder::class,
            SecurityLogSeeder::class,
            InputSeeder::class,
            OrderSeeder::class,
            ThreadSeeder::class,
            MessageSeeder::class,
            DisputeSeeder::class,
            CouponSeeder::class,
        ]);
        

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║  ✓ SEED COMPLETO COM SUCESSO!           ║');
        $this->command->info('╠══════════════════════════════════════════╣');
        $this->command->info('║  Senha padrão: Mudar@123                  ║');
        $this->command->info('║                                          ║');
        $this->command->info('║  Admin:     admin@teste.com               ║');
        $this->command->info('║  Loja 1:    loja1@teste.com (seller1)     ║');
        $this->command->info('║  Loja 2:    loja2@teste.com (seller2)     ║');
        $this->command->info('║  Transp 1:  transp1@teste.com (carrier1)  ║');
        $this->command->info('║  Transp 2:  transp2@teste.com (carrier2)  ║');
        $this->command->info('║  Clientes:  cliente1..5@teste.com         ║');
        $this->command->info('╚══════════════════════════════════════════╝');
    }
}