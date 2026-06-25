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
     * Ordem: Users → Clients → Orders → Inputs → Products
     */
    public function run(): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║          DEMANDA3D — SEED INICIADO        ║');
        $this->command->info('╚══════════════════════════════════════════╝');
        $this->command->info('');

        $this->call([
            UserSeeder::class,
            ClientSeeder::class,
            //ProductSeeder::class,
            InputSeeder::class,
            OrderSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════╗');
        $this->command->info('║  ✓ SEED COMPLETO COM SUCESSO!           ║');
        $this->command->info('╠══════════════════════════════════════════╣');
        $this->command->info('║  Admin:   admin@demanda3d.com           ║');
        $this->command->info('║  Senha:   Mudar@123                     ║');
        $this->command->info('║                                          ║');
        $this->command->info('║  Partners: tech3d, maker, prototype      ║');
        $this->command->info('║  Customer: cliente@demanda3d.com.br      ║');
        $this->command->info('║  Senha:   Mudar@123                     ║');
        $this->command->info('╚══════════════════════════════════════════╝');
    }
}