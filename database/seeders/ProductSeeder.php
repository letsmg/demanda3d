<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use App\Services\ImageOptimizationService;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    private const MAX_IMAGES_PER_PRODUCT = 3;

    public function run(): void
    {
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command?->warn('⚠ Nenhum tenant encontrado.');

            return;
        }

        $this->command?->info('=== Criando produtos e baixando imagens ===');

        $imageService = app(ImageOptimizationService::class);

        $products = [
            [
                'name' => 'Suporte para smartphone ABS',
                'description' => 'Suporte universal para smartphone, compatível com modelos de 4 a 7 polegadas. Fabricado em ABS de alta resistência.',
                'categories' => ['escritorio', 'utilitarios'],
                'height' => 80, 'width' => 60, 'approximate_weight' => 45, 'waste_weight' => 8,
                'material_type' => 'filament', 'print_time' => 120, 'pieces_produced' => 4,
                'maintenance_fee' => 3.50, 'painting_time' => null, 'painting_material' => null,
                'painting_cost' => 0.00, 'extras_cost' => 1.50, 'approximate_cost' => 14.90, 'sale_price' => 45.90,
            ],
            [
                'name' => 'Porta-chaves personalizado PLA',
                'description' => 'Porta-chaves impresso em PLA com design personalizável.',
                'categories' => ['decorativo', 'utilitarios', 'personagens'],
                'height' => 50, 'width' => 30, 'approximate_weight' => 15, 'waste_weight' => 5,
                'material_type' => 'filament', 'print_time' => 45, 'pieces_produced' => 10,
                'maintenance_fee' => 1.20, 'painting_time' => 20, 'painting_material' => 'Tinta acrílica',
                'painting_cost' => 2.50, 'extras_cost' => 0.80, 'approximate_cost' => 8.40, 'sale_price' => 25.50,
            ],
            [
                'name' => 'Organizador de mesa PETG',
                'description' => 'Organizador modular para mesa de escritório.',
                'categories' => ['escritorio', 'utilitarios'],
                'height' => 120, 'width' => 180, 'approximate_weight' => 200, 'waste_weight' => 25,
                'material_type' => 'filament', 'print_time' => 480, 'pieces_produced' => 1,
                'maintenance_fee' => 12.00, 'painting_time' => null, 'painting_material' => null,
                'painting_cost' => 0.00, 'extras_cost' => 3.00, 'approximate_cost' => 42.90, 'sale_price' => 89.90,
            ],
            [
                'name' => 'Vaso decorativo geométrico PLA',
                'description' => 'Vaso com design geométrico moderno para decoração.',
                'categories' => ['decorativo', 'cozinha'],
                'height' => 150, 'width' => 100, 'approximate_weight' => 120, 'waste_weight' => 15,
                'material_type' => 'filament', 'print_time' => 360, 'pieces_produced' => 1,
                'maintenance_fee' => 8.00, 'painting_time' => 30, 'painting_material' => 'Spray primer',
                'painting_cost' => 5.00, 'extras_cost' => 2.00, 'approximate_cost' => 28.50, 'sale_price' => 35.00,
            ],
            [
                'name' => 'Engrenagem para protótipo funcional',
                'description' => 'Engrenagem industrial em Nylon reforçado para prototipagem rápida.',
                'categories' => ['automotivo', 'utilitarios'],
                'height' => 40, 'width' => 40, 'approximate_weight' => 25, 'waste_weight' => 10,
                'material_type' => 'filament', 'print_time' => 90, 'pieces_produced' => 6,
                'maintenance_fee' => 5.50, 'painting_time' => null, 'painting_material' => null,
                'painting_cost' => 0.00, 'extras_cost' => 1.00, 'approximate_cost' => 22.00, 'sale_price' => 120.00,
            ],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $this->command?->info("  ── Tenant: {$tenant->display_name} ──");

            foreach ($products as $pd) {
                $slug = Product::generateUniqueSlug($pd['name'], $tenantId);

                $product = Product::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'name' => $pd['name'],
                    ],
                    [
                        'slug' => $slug,
                        'description' => $pd['description'],
                        'height' => $pd['height'],
                        'width' => $pd['width'],
                        'approximate_weight' => $pd['approximate_weight'],
                        'waste_weight' => $pd['waste_weight'],
                        'material_type' => $pd['material_type'],
                        'print_time' => $pd['print_time'],
                        'pieces_produced' => $pd['pieces_produced'],
                        'maintenance_fee' => $pd['maintenance_fee'],
                        'painting_time' => $pd['painting_time'],
                        'painting_material' => $pd['painting_material'],
                        'painting_cost' => $pd['painting_cost'],
                        'extras_cost' => $pd['extras_cost'],
                        'approximate_cost' => $pd['approximate_cost'],
                        'sale_price' => $pd['sale_price'],
                        'is_active' => true,
                        'moderation_status' => 'approved',
                    ]
                );

                $slugs = $pd['categories'] ?? [];
                if ($slugs) {
                    $ids = Category::whereIn('slug', $slugs)->pluck('id')->toArray();
                    if ($ids) { $product->categories()->sync($ids); }
                }

                $this->command?->line("    ✓ Produto atualizado/criado: {$product->name}");

                // Remove imagens antigas para garantir exatamente MAX_IMAGES_PER_PRODUCT
                $oldImages = ProductImage::where('product_id', $product->id)->get();
                foreach ($oldImages as $old) {
                    Storage::disk('public')->delete([$old->path, $old->original_path, $old->thumbnail_path]);
                    $old->delete();
                }

                for ($i = 0; $i < self::MAX_IMAGES_PER_PRODUCT; $i++) {
                    $seed = $product->id . '-' . $i;
                    $url = "https://picsum.photos/seed/{$seed}/800/800";
                    $this->command?->getOutput()->write("    ⏳ Baixando imagem " . ($i + 1) . "/" . self::MAX_IMAGES_PER_PRODUCT . "... ");
                    $content = @file_get_contents($url);

                    if ($content === false) {
                        $this->command?->getOutput()->writeln('<fg=red>✗ FALHA</>');

                        continue;
                    }

                    $tmpPath = tempnam(sys_get_temp_dir(), 'seed_') . '.jpg';
                    file_put_contents($tmpPath, $content);

                    $uploadedFile = new UploadedFile($tmpPath, "seed-{$i}.jpg", 'image/jpeg', null, true);

                    try {
                        $result = $imageService->processProductUpload(
                            $uploadedFile,
                            $tenantId,
                            $product->id,
                            $product->slug . '-' . ($i + 1),
                        );

                        ProductImage::create([
                            'product_id' => $product->id,
                            'path' => $result['optimized_path'],
                            'original_path' => $result['original_path'],
                            'thumbnail_path' => $result['thumbnail_path'],
                            'order' => $i,
                        ]);

                        $this->command?->getOutput()->writeln('<fg=green>✓ OK</>');
                    } catch (\Exception $e) {
                        $this->command?->getOutput()->writeln("<fg=red>✗ ERRO: {$e->getMessage()}</>");
                    } finally {
                        @unlink($tmpPath);
                    }
                }
            }
            $this->command?->info('');
        }
    }
}