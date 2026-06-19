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
            'first_name' => 'Admin',
            'last_name' => 'Master',
            'email' => 'admin@demanda3d.com',
            'password' => 'Mudar@123',
        ]);

        // Create partner user
        User::factory()->partner()->create([
            'first_name' => 'Partner',
            'last_name' => 'Usuário',
            'email' => 'partner@demanda3d.com.br',
        ]);

        // Create customer user
        User::factory()->customer()->create([
            'first_name' => 'Cliente',
            'last_name' => 'Teste',
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