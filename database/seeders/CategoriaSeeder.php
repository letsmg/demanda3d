<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
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

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'is_adult' => $cat['is_adult']],
            );
        }
    }
}