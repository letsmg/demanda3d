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
        // Get all tenants (admin + partners)
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
                        'price_sale' => $productData['price_sale'],
                        'discount_cash' => $productData['discount_cash'],
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
                //comentar abaixo para nao seedar imagens
                //Download missing images
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