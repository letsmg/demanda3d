<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Get all tenants (admin + management)
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('⚠ Nenhum tenant encontrado. Produtos não serão criados.');
            return;
        }

        $this->command->info('=== Criando produtos e baixando imagens ===');

        $products = [
            [
                'name' => 'Suporte para smartphone ABS',
                'description' => 'Suporte universal para smartphone, compatível com modelos de 4 a 7 polegadas. Fabricado em ABS de alta resistência.',
                'height' => 80,
                'width' => 60,
                'approximate_weight' => 45,
                'waste_weight' => 8,
                'material_type' => 'filament',
                'print_time' => 120,
                'pieces_produced' => 4,
                'maintenance_fee' => 3.50,
                'painting_time' => null,
                'painting_material' => null,
                'painting_cost' => 0.00,
                'extras_cost' => 1.50,
                'approximate_cost' => 14.90,
                'sale_price' => 45.90,
            ],
            [
                'name' => 'Porta-chaves personalizado PLA',
                'description' => 'Porta-chaves impresso em PLA com design personalizável. Ideal para brindes corporativos.',
                'height' => 50,
                'width' => 30,
                'approximate_weight' => 15,
                'waste_weight' => 5,
                'material_type' => 'filament',
                'print_time' => 45,
                'pieces_produced' => 10,
                'maintenance_fee' => 1.20,
                'painting_time' => 20,
                'painting_material' => 'Tinta acrílica',
                'painting_cost' => 2.50,
                'extras_cost' => 0.80,
                'approximate_cost' => 8.40,
                'sale_price' => 25.50,
            ],
            [
                'name' => 'Organizador de mesa PETG',
                'description' => 'Organizador modular para mesa de escritório. Compartimentos para canetas, clips e post-its.',
                'height' => 120,
                'width' => 180,
                'approximate_weight' => 200,
                'waste_weight' => 25,
                'material_type' => 'filament',
                'print_time' => 480,
                'pieces_produced' => 1,
                'maintenance_fee' => 12.00,
                'painting_time' => null,
                'painting_material' => null,
                'painting_cost' => 0.00,
                'extras_cost' => 3.00,
                'approximate_cost' => 42.90,
                'sale_price' => 89.90,
            ],
            [
                'name' => 'Vaso decorativo geométrico PLA',
                'description' => 'Vaso com design geométrico moderno para decoração. Disponível em diversas cores.',
                'height' => 150,
                'width' => 100,
                'approximate_weight' => 120,
                'waste_weight' => 15,
                'material_type' => 'filament',
                'print_time' => 360,
                'pieces_produced' => 1,
                'maintenance_fee' => 8.00,
                'painting_time' => 30,
                'painting_material' => 'Spray primer',
                'painting_cost' => 5.00,
                'extras_cost' => 2.00,
                'approximate_cost' => 28.50,
                'sale_price' => 35.00,
            ],
            [
                'name' => 'Engrenagem para protótipo funcional',
                'description' => 'Engrenagem industrial em Nylon reforçado para prototipagem rápida. Alta precisão dimensional.',
                'height' => 40,
                'width' => 40,
                'approximate_weight' => 25,
                'waste_weight' => 10,
                'material_type' => 'filament',
                'print_time' => 90,
                'pieces_produced' => 6,
                'maintenance_fee' => 5.50,
                'painting_time' => null,
                'painting_material' => null,
                'painting_cost' => 0.00,
                'extras_cost' => 1.00,
                'approximate_cost' => 22.00,
                'sale_price' => 120.00,
            ],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $tenantDisplay = $tenant->display_name ?? "Tenant #{$tenantId}";
            $this->command->info("  ── Tenant: {$tenantDisplay} ──");

            foreach ($products as $productData) {
                // Check if product already exists for this tenant
                $product = Product::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where('name', $productData['name'])
                    ->first();

                if (!$product) {
                    $product = Product::withoutGlobalScopes()->create([
                        'tenant_id' => $tenantId,
                        'name' => $productData['name'],
                        'description' => $productData['description'],
                        'height' => $productData['height'],
                        'width' => $productData['width'],
                        'approximate_weight' => $productData['approximate_weight'],
                        'waste_weight' => $productData['waste_weight'],
                        'material_type' => $productData['material_type'],
                        'print_time' => $productData['print_time'],
                        'pieces_produced' => $productData['pieces_produced'],
                        'maintenance_fee' => $productData['maintenance_fee'],
                        'painting_time' => $productData['painting_time'],
                        'painting_material' => $productData['painting_material'],
                        'painting_cost' => $productData['painting_cost'],
                        'extras_cost' => $productData['extras_cost'],
                        'approximate_cost' => $productData['approximate_cost'],
                        'sale_price' => $productData['sale_price'],
                        'is_active' => true,
                    ]);
                    $this->command->line("    ✓ Produto criado: {$product->name}");
                } else {
                    $this->command->line("    → Produto já existe: {$product->name}");
                }

                // Check existing images for this product
                $existingCount = ProductImage::where('product_id', $product->id)->count();
                $neededImages = 5 - $existingCount;

                if ($neededImages <= 0) {
                    $this->command->line("    → {$existingCount} imagens já existentes, pulando download");
                    continue;
                }

                // Download missing images
                for ($i = $existingCount; $i < 5; $i++) {
                    $imageUrl = "https://picsum.photos/seed/{$product->id}-{$i}/800/800";
                    $filename = "products/{$tenantId}/{$product->id}-{$i}.jpg";
                    $this->command->getOutput()->write("    ⏳ Baixando imagem {$i}/4... ");

                    $imageContent = @file_get_contents($imageUrl);

                    if ($imageContent !== false) {
                        Storage::disk('public')->put($filename, $imageContent);

                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $filename,
                            'order' => $i,
                        ]);
                        $this->command->getOutput()->writeln("<fg=green>✓ OK</>");
                    } else {
                        $this->command->getOutput()->writeln("<fg=red>✗ FALHA</>");
                        $this->command->warn("      Não foi possível baixar imagem de {$imageUrl}");
                    }
                }
            }
            $this->command->info('');
        }
    }
}