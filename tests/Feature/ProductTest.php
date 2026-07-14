<?php

use App\Models\Product;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;


beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $this->seller1 = User::factory()->seller1()->create();
    $this->seller1->tenant()->create([
        'company_name_encrypted' => $makeEncr('Co')['encrypted'],
        'company_name_hash' => $makeEncr('Co')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'address_encrypted' => $makeEncr('Rua')['encrypted'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '00000-000', 'active' => true,
    ]);
    $this->customer = User::factory()->customer()->create();
});

test('management can create product with 3d printing fields', function () {
    $product = Product::factory()->create([
        'tenant_id' => $this->seller1->tenant->id,
        'name' => 'Vaso Geométrico',
        'description' => 'Vaso decorativo',
        'sale_price' => 45.90,
        'height' => 150,
        'width' => 100,
        'approximate_weight' => 120,
        'waste_weight' => 15,
        'material_type' => 'filament',
        'print_time' => 360,
        'pieces_produced' => 1,
        'maintenance_fee' => 8.00,
        'painting_time' => null,
        'painting_material' => null,
        'painting_cost' => null,
        'extras_cost' => 2.00,
        'approximate_cost' => 28.50,
    ]);

    expect($product->id)->not->toBeNull();
    expect($product->material_type)->toBe('filament');
    expect($product->print_time)->toBe(360);
    expect($product->approximate_weight)->toBe(120);
    expect($product->waste_weight)->toBe(15);
    expect($product->painting_time)->toBeNull();
    expect($product->painting_cost)->toBeNull();
    expect((float) $product->sale_price)->toBe(45.90);
});

test('product nullable painting fields accepted', function () {
    $product = Product::factory()->create([
        'painting_time' => null,
        'painting_material' => null,
        'painting_cost' => null,
        'tenant_id' => $this->seller1->tenant->id,
    ]);
    expect($product->painting_time)->toBeNull();
    expect($product->painting_material)->toBeNull();
    expect($product->painting_cost)->toBeNull();
});

test('customer cannot create product', function () {
    $response = actingAs($this->customer)->postJson('/api/produtos', [
        'name' => 'Teste',
        'sale_price' => 10,
    ]);

    expect(in_array($response->status(), [403, 405]))->toBeTrue();
});

test('product name unique per tenant', function () {
    Product::factory()->create([
        'name' => 'Vaso Único',
        'tenant_id' => $this->seller1->tenant->id,
    ]);

    expect(fn () => Product::factory()->create([
        'name' => 'Vaso Único',
        'tenant_id' => $this->seller1->tenant->id,
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('product creation auto-generates SEO fields via service', function () {
    actingAs($this->seller1);

    $mockModeration = Mockery::mock(\App\Services\ImageModerationService::class);
    $mockModeration->shouldReceive('moderateUpload')->andReturn(['adult_category_id' => null, 'status' => 'approved', 'category' => 'safe']);
    \Illuminate\Support\Facades\App::instance(\App\Services\ImageModerationService::class, $mockModeration);

    $service = app(\App\Services\ProductService::class);
    $product = $service->create([
        'name' => 'Produto SEO Teste',
        'description' => 'Descrição do produto para teste de SEO automático.',
        'sale_price' => 99.90,
        'material_type' => 'PLA',
        'height' => 100,
        'width' => 50,
        'approximate_weight' => 200,
    ]);

    expect($product->meta_title)->toBe('Produto SEO Teste');
    expect($product->meta_description)->toBe('Descrição do produto para teste de SEO automático.');
    expect($product->meta_keywords)->toContain('produto seo teste')
        ->toContain('impressão 3d');

    // schema_markup é gerado dinamicamente via accessor (não mais armazenado no banco)
    $schema = json_decode($product->schema_markup, true);
    expect($schema)->not->toBeNull();
    expect($schema['@type'])->toBe('Product');
    expect($schema['name'])->toBe('Produto SEO Teste');
    expect($schema['offers']['price'])->toBe('99.9');
    expect($schema['offers']['priceCurrency'])->toBe('BRL');
    expect($schema['offers']['availability'])->toBe('https://schema.org/InStock');
    expect($schema['additionalProperty'])->toHaveCount(3); // height, width, weight

    // google_tag_manager é gerado dinamicamente via accessor
    expect($product->google_tag_manager)->toContain('Google Tag Manager');
    expect($product->google_tag_manager)->toContain('product_detail_view');
    expect($product->google_tag_manager)->toContain('Produto SEO Teste');
});

test('product SEO fields are always derived dynamically from native attributes', function () {
    actingAs($this->seller1);

    $mockModeration = Mockery::mock(\App\Services\ImageModerationService::class);
    $mockModeration->shouldReceive('moderateUpload')->andReturn(['adult_category_id' => null, 'status' => 'approved', 'category' => 'safe']);
    \Illuminate\Support\Facades\App::instance(\App\Services\ImageModerationService::class, $mockModeration);

    $service = app(\App\Services\ProductService::class);
    $product = $service->create([
        'name' => 'Produto Manual',
        'description' => 'Descrição manual.',
        'sale_price' => 49.90,
    ]);

    // Todos os campos SEO são derivados de name/description — sempre
    expect($product->meta_title)->toBe('Produto Manual');
    expect($product->meta_description)->toBe('Descrição manual.');
    expect($product->meta_keywords)->toContain('produto manual');

    // schema_markup e google_tag_manager são sempre gerados dinamicamente
    $schema = json_decode($product->schema_markup, true);
    expect($schema)->not->toBeNull();
    expect($schema['@type'])->toBe('Product');
    expect($schema['name'])->toBe('Produto Manual');

    expect($product->google_tag_manager)->toContain('Produto Manual');
});

test('product SEO fields update correctly when name changes', function () {
    actingAs($this->seller1);

    $mockModeration = Mockery::mock(\App\Services\ImageModerationService::class);
    $mockModeration->shouldReceive('moderateUpload')->andReturn(['adult_category_id' => null, 'status' => 'approved', 'category' => 'safe']);
    \Illuminate\Support\Facades\App::instance(\App\Services\ImageModerationService::class, $mockModeration);

    $service = app(\App\Services\ProductService::class);
    $product = $service->create([
        'name' => 'Produto Original',
        'description' => 'Descrição original.',
        'sale_price' => 29.90,
    ]);

    $originalTitle = $product->meta_title;

    // Update only name — SEO fields should be regenerated since they're empty in request
    $service->update($product, [
        'name' => 'Produto Renomeado',
        'description' => 'Descrição original.',
        'sale_price' => 29.90,
    ]);

    $product->refresh();
    expect($product->meta_title)->toBe('Produto Renomeado');
    expect($product->meta_title)->not->toBe($originalTitle);

    // schema_markup and google_tag_manager reflect new name (dinâmicos via accessor)
    expect($product->schema_markup)->toContain('Produto Renomeado');
    expect($product->google_tag_manager)->toContain('Produto Renomeado');
});

test('product seeder generates valid schema markup and GTM via accessors', function () {
    // Needs CategorySeeder first
    $categorySeeder = new \Database\Seeders\CategorySeeder();
    $categorySeeder->run();

    $seeder = new \Database\Seeders\ProductSeeder();
    $seeder->run();

    $products = Product::withoutGlobalScopes()->get();

    foreach ($products as $product) {
        // schema_markup is a dynamic accessor — must be valid JSON
        $schema = json_decode($product->schema_markup, true);
        expect($schema)->not->toBeNull("Schema inválido para produto {$product->name}");
        expect($schema['@type'])->toBe('Product');
        expect($schema['name'])->toBe($product->name);
        expect($schema['offers']['priceCurrency'])->toBe('BRL');

        // google_tag_manager is a dynamic accessor — must contain product name and dataLayer
        expect($product->google_tag_manager)->toContain('Google Tag Manager');
        expect($product->google_tag_manager)->toContain('product_detail_view');
        expect($product->google_tag_manager)->toContain($product->name);
    }
});