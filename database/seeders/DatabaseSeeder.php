<?php

namespace Database\Seeders;

use App\Enums\UserAccessLevel;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash as HashFacade;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $userService = app(UserService::class);

        // Create admin user
        $userService->create([
            'first_name' => 'Admin',
            'last_name' => 'Master',
            'display_name' => 'Admin Master',
            'email' => 'admin@demanda3d.com',
            'password' => HashFacade::make('Mudar@123'),
            'access_level' => UserAccessLevel::ADMIN,
        ]);

        // Create partner user
        $userService->create([
            'first_name' => 'Partner',
            'last_name' => 'Usuário',
            'display_name' => 'Partner Usuário',
            'email' => 'partner@demanda3d.com.br',
            'password' => HashFacade::make('Mudar@123'),
            'access_level' => UserAccessLevel::PARTNER,
        ]);

        // Create customer user
        $userService->create([
            'first_name' => 'Cliente',
            'last_name' => 'Teste',
            'display_name' => 'Cliente Teste',
            'email' => 'cliente@demanda3d.com.br',
            'password' => HashFacade::make('Mudar@123'),
            'access_level' => UserAccessLevel::CUSTOMER,
        ]);

        // Create clients, orders, inputs and products
        $this->call([
            ClientSeeder::class,
            OrderSeeder::class,
            InputSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
