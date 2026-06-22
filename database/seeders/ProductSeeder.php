<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users that have tenants
        $users = User::has('tenant')->get();

        $products = [
            [
                'name' => 'Suporte para smartphone ABS',
                'description' => 'Suporte universal para smartphone, compatível com modelos de 4 a 7 polegadas. Fabricado em ABS de alta resistência.',
                'price_sale' => 45.90,
                'discount_cash' => 10,
            ],
            [
                'name' => 'Porta-chaves personalizado PLA',
                'description' => 'Porta-chaves impresso em PLA com design personalizável. Ideal para brindes corporativos.',
                'price_sale' => 25.50,
                'discount_cash' => 15,
            ],
            [
                'name' => 'Organizador de mesa PETG',
                'description' => 'Organizador modular para mesa de escritório. Compartimentos para canetas, clips e post-its.',
                'price_sale' => 89.90,
                'discount_cash' => 5,
            ],
            [
                'name' => 'Vaso decorativo geométrico PLA',
                'description' => 'Vaso com design geométrico moderno para decoração. Disponível em diversas cores.',
                'price_sale' => 35.00,
                'discount_cash' => 20,
            ],
            [
                'name' => 'Engrenagem para protótipo funcional',
                'description' => 'Engrenagem industrial em Nylon reforçado para prototipagem rápida. Alta precisão dimensional.',
                'price_sale' => 120.00,
                'discount_cash' => 8,
            ],
        ];

        foreach ($users as $user) {
            $tenantId = $user->tenant->id;

            foreach ($products as $product) {
                Product::factory()->create([
                    'tenant_id' => $tenantId,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price_sale' => $product['price_sale'],
                    'discount_cash' => $product['discount_cash'],
                    'is_active' => true,
                ]);
            }
        }
    }
}