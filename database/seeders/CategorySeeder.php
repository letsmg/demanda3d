<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Escritório', 'slug' => 'escritorio', 'is_adult' => false],
            ['name' => 'Cozinha', 'slug' => 'cozinha', 'is_adult' => false],
            ['name' => 'Banho', 'slug' => 'banho', 'is_adult' => false],
            ['name' => 'Decorativo', 'slug' => 'decorativo', 'is_adult' => false],
            ['name' => 'Adulto', 'slug' => 'adulto', 'is_adult' => true],
            ['name' => 'Personagens', 'slug' => 'personagens', 'is_adult' => false],
            ['name' => 'Animais', 'slug' => 'animais', 'is_adult' => false],
            ['name' => 'Utilitários', 'slug' => 'utilitarios', 'is_adult' => false],
            ['name' => 'Hidráulico', 'slug' => 'hidraulico', 'is_adult' => false],
            ['name' => 'Automotivo', 'slug' => 'automotivo', 'is_adult' => false],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'is_adult' => $cat['is_adult']],
            );
        }
    }
}