<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaSeeder extends Seeder
{
    /**
     * Seed das categorias do marketplace.
     */
    public function run(): void
    {
        $categorias = [
            ['nome' => 'Escritório', 'slug' => 'escritorio', 'maior_de_idade' => false],
            ['nome' => 'Cozinha', 'slug' => 'cozinha', 'maior_de_idade' => false],
            ['nome' => 'Banho', 'slug' => 'banho', 'maior_de_idade' => false],
            ['nome' => 'Decorativo', 'slug' => 'decorativo', 'maior_de_idade' => false],
            ['nome' => 'Adulto', 'slug' => 'adulto', 'maior_de_idade' => true],
            ['nome' => 'Personagens', 'slug' => 'personagens', 'maior_de_idade' => false],
            ['nome' => 'Animais', 'slug' => 'animais', 'maior_de_idade' => false],
            ['nome' => 'Utilitários', 'slug' => 'utilitarios', 'maior_de_idade' => false],
            ['nome' => 'Hidráulico', 'slug' => 'hidraulico', 'maior_de_idade' => false],
            ['nome' => 'Automotivo', 'slug' => 'automotivo', 'maior_de_idade' => false],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'nome' => $cat['nome'],
                    'maior_de_idade' => $cat['maior_de_idade'],
                ]
            );
        }
    }
}