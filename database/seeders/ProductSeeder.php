<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

        $products = [
            [
                'name' => 'Suporte para smartphone ABS',
                'description' => 'Suporte universal para smartphone, compatível com modelos de 4 a 7 polegadas. Fabricado em ABS de alta resistência.',
                'categorias' => ['escritorio', 'utilitarios'],
                'height' => 80, 'width' => 60, 'approximate_weight' => 45, 'waste_weight' => 8,
                'material_type' => 'filament', 'print_time' => 120, 'pieces_produced' => 4,
                'maintenance_fee' => 3.50, 'painting_time' => null, 'painting_material' => null,
                'painting_cost' => 0.00, 'extras_cost' => 1.50, 'approximate_cost' => 14.90, 'sale_price' => 45.90,
                'meta_title' => 'Suporte para Smartphone ABS 3D | Demanda3D',
                'meta_description' => 'Suporte universal para smartphone impresso em ABS.',
                'meta_keywords' => 'suporte smartphone, ABS, impressão 3D',
            ],
            [
                'name' => 'Porta-chaves personalizado PLA',
                'description' => 'Porta-chaves impresso em PLA com design personalizável.',
                'categorias' => ['decorativo', 'utilitarios', 'personagens'],
                'height' => 50, 'width' => 30, 'approximate_weight' => 15, 'waste_weight' => 5,
                'material_type' => 'filament', 'print_time' => 45, 'pieces_produced' => 10,
                'maintenance_fee' => 1.20, 'painting_time' => 20, 'painting_material' => 'Tinta acrílica',
                'painting_cost' => 2.50, 'extras_cost' => 0.80, 'approximate_cost' => 8.40, 'sale_price' => 25.50,
                'meta_title' => 'Porta-Chaves Personalizado PLA 3D | Demanda3D',
                'meta_description' => 'Porta-chaves impresso em PLA com design personalizável.',
                'meta_keywords' => 'porta chaves, PLA, impressão 3D',
            ],
            [
                'name' => 'Organizador de mesa PETG',
                'description' => 'Organizador modular para mesa de escritório.',
                'categorias' => ['escritorio', 'utilitarios'],
                'height' => 120, 'width' => 180, 'approximate_weight' => 200, 'waste_weight' => 25,
                'material_type' => 'filament', 'print_time' => 480, 'pieces_produced' => 1,
                'maintenance_fee' => 12.00, 'painting_time' => null, 'painting_material' => null,
                'painting_cost' => 0.00, 'extras_cost' => 3.00, 'approximate_cost' => 42.90, 'sale_price' => 89.90,
                'meta_title' => 'Organizador de Mesa PETG Modular 3D | Demanda3D',
                'meta_description' => 'Organizador modular para mesa de escritório em PETG.',
                'meta_keywords' => 'organizador mesa, PETG, impressão 3D',
            ],
            [
                'name' => 'Vaso decorativo geométrico PLA',
                'description' => 'Vaso com design geométrico moderno para decoração.',
                'categorias' => ['decorativo', 'cozinha'],
                'height' => 150, 'width' => 100, 'approximate_weight' => 120, 'waste_weight' => 15,
                'material_type' => 'filament', 'print_time' => 360, 'pieces_produced' => 1,
                'maintenance_fee' => 8.00, 'painting_time' => 30, 'painting_material' => 'Spray primer',
                'painting_cost' => 5.00, 'extras_cost' => 2.00, 'approximate_cost' => 28.50, 'sale_price' => 35.00,
                'meta_title' => 'Vaso Geométrico Decorativo PLA 3D | Demanda3D',
                'meta_description' => 'Vaso com design geométrico moderno em PLA.',
                'meta_keywords' => 'vaso decorativo, geométrico, PLA, impressão 3D',
            ],
            [
                'name' => 'Engrenagem para protótipo funcional',
                'description' => 'Engrenagem industrial em Nylon reforçado para prototipagem rápida.',
                'categorias' => ['automotivo', 'utilitarios'],
                'height' => 40, 'width' => 40, 'approximate_weight' => 25, 'waste_weight' => 10,
                'material_type' => 'filament', 'print_time' => 90, 'pieces_produced' => 6,
                'maintenance_fee' => 5.50, 'painting_time' => null, 'painting_material' => null,
                'painting_cost' => 0.00, 'extras_cost' => 1.00, 'approximate_cost' => 22.00, 'sale_price' => 120.00,
                'meta_title' => 'Engrenagem Protótipo Nylon 3D | Demanda3D',
                'meta_description' => 'Engrenagem industrial em Nylon reforçado para prototipagem rápida.',
                'meta_keywords' => 'engrenagem, protótipo, nylon, impressão 3D',
            ],
        ];

        foreach ($tenants as $tenant) {
            $tenantId = $tenant->id;
            $this->command?->info("  ── Tenant: {$tenant->display_name} ──");

            foreach ($products as $pd) {
                $product = Product::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)->where('name', $pd['name'])->first();

                if (! $product) {
                    $product = Product::withoutGlobalScopes()->create([
                        'tenant_id'          => $tenantId,
                        'name'               => $pd['name'],
                        'slug'               => Product::generateUniqueSlug($pd['name'], $tenantId),
                        'description'        => $pd['description'],
                        'height'             => $pd['height'],
                        'width'              => $pd['width'],
                        'approximate_weight' => $pd['approximate_weight'],
                        'waste_weight'       => $pd['waste_weight'],
                        'material_type'      => $pd['material_type'],
                        'print_time'         => $pd['print_time'],
                        'pieces_produced'    => $pd['pieces_produced'],
                        'maintenance_fee'    => $pd['maintenance_fee'],
                        'painting_time'      => $pd['painting_time'],
                        'painting_material'  => $pd['painting_material'],
                        'painting_cost'      => $pd['painting_cost'],
                        'extras_cost'        => $pd['extras_cost'],
                        'approximate_cost'   => $pd['approximate_cost'],
                        'sale_price'         => $pd['sale_price'],
                        'is_active'          => true,
                        'moderation_status'  => 'approved',
                        'meta_title'         => $pd['meta_title'],
                        'meta_description'   => $pd['meta_description'],
                        'meta_keywords'      => $pd['meta_keywords'],
                        'schema_markup'      => $this->generateSchemaMarkup($pd),
                        'google_tag_manager' => $this->generateGtmScript($pd),
                    ]);

                    $slugs = $pd['categorias'] ?? [];
                    if ($slugs) {
                        $ids = Categoria::whereIn('slug', $slugs)->pluck('id')->toArray();
                        if ($ids) { $product->categorias()->sync($ids); }
                    }

                    $this->command?->line("    ✓ Produto criado: {$product->name}");
                }

                $existing = ProductImage::where('product_id', $product->id)->count();
                $need = self::MAX_IMAGES_PER_PRODUCT - $existing;

                for ($i = $existing; $i < self::MAX_IMAGES_PER_PRODUCT; $i++) {
                    $url = "https://picsum.photos/seed/{$product->id}-{$i}/800/800";
                    $path = "products/{$tenantId}/{$product->id}-{$i}.jpg";
                    $this->command?->getOutput()->write("    ⏳ Baixando imagem {$i}/2... ");
                    $content = @file_get_contents($url);

                    if ($content !== false) {
                        Storage::disk('public')->put($path, $content);
                        ProductImage::create(['product_id' => $product->id, 'path' => $path, 'order' => $i]);
                        $this->command?->getOutput()->writeln("<fg=green>✓ OK</>");
                    } else {
                        $this->command?->getOutput()->writeln("<fg=red>✗ FALHA</>");
                    }
                }
            }
            $this->command?->info('');
        }
    }

    private function generateSchemaMarkup(array $pd): string
    {
        $schema = [
            '@context' => 'https://schema.org', '@type' => 'Product',
            'name' => $pd['name'],
            'description' => mb_substr(strip_tags($pd['description']), 0, 5000),
            'sku' => Str::slug($pd['name']),
            'offers' => [
                '@type' => 'Offer', 'price' => (string) $pd['sale_price'],
                'priceCurrency' => 'BRL', 'availability' => 'https://schema.org/InStock',
                'priceValidUntil' => now()->addYear()->format('Y-m-d'),
            ],
        ];

        if ($pd['height'] || $pd['width'] || $pd['approximate_weight']) {
            $schema['additionalProperty'] = [];
            if ($pd['height']) { $schema['additionalProperty'][] = ['@type' => 'PropertyValue', 'name' => 'Altura', 'value' => $pd['height'] . ' mm']; }
            if ($pd['width']) { $schema['additionalProperty'][] = ['@type' => 'PropertyValue', 'name' => 'Largura', 'value' => $pd['width'] . ' mm']; }
            if ($pd['approximate_weight']) { $schema['additionalProperty'][] = ['@type' => 'PropertyValue', 'name' => 'Peso', 'value' => $pd['approximate_weight'] . ' g']; }
        }

        if (! empty($pd['categorias'])) {
            $names = Categoria::whereIn('slug', $pd['categorias'])->pluck('name')->toArray();
            if ($names) { $schema['category'] = implode(', ', $names); }
        }

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function generateGtmScript(array $pd): string
    {
        $json = json_encode(['event' => 'product_detail_view', 'ecommerce' => ['detail' => ['products' => [['name' => $pd['name'], 'price' => (string) $pd['sale_price']]]]]], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return "<!-- Google Tag Manager -->\n<script>\n  window.dataLayer = window.dataLayer || [];\n  dataLayer.push({$json});\n</script>";
    }
}