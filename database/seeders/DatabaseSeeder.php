<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Admin Master',
            'email' => 'admin@demanda3d.com',
            'password' => 'Mudar@123',
        ]);

        // Create staff user
        User::factory()->staff()->create([
            'name' => 'Staff Usuário',
            'email' => 'staff@demanda3d.com.br',
        ]);

        // Create customer user
        User::factory()->customer()->create([
            'name' => 'Cliente Teste',
            'email' => 'cliente@demanda3d.com.br',
        ]);

        // Create random users
        User::factory()->count(7)->create();

        // Create clients, orders and inputs
        $this->call([
            ClientSeeder::class,
            OrderSeeder::class,
            InputSeeder::class,
        ]);
    }
}