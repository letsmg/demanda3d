<?php

use App\Models\Product;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);
    $this->management = User::factory()->management()->create();
    $this->management->tenant()->create([
        'company_name_encrypted' => $makeEncr('Co')['encrypted'],
        'company_name_hash' => $makeEncr('Co')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'phone_hash' => $makeEncr('11999999999')['hash'],
        'address_encrypted' => $makeEncr('Rua')['encrypted'],
        'address_hash' => $makeEncr('Rua')['hash'],
        'number_encrypted' => $makeEncr('1')['encrypted'],
        'number_hash' => $makeEncr('1')['hash'],
        'city_encrypted' => $makeEncr('SP')['encrypted'],
        'city_hash' => $makeEncr('SP')['hash'],
        'state' => 'SP', 'zipcode' => '00000-000', 'active' => true,
    ]);
    $this->customer = User::factory()->customer()->create();
});

test('management can create product with 3d printing fields', function () {
    $response = actingAs($this->management)->postJson('/api/produtos', [
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

    $response->assertStatus(201);
    $product = Product::first();
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
        'tenant_id' => $this->management->tenant->id,
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

    // POST not available on this route (only GET for public API)
    // Either 405 (Method Not Allowed) or 403 (Forbidden) are acceptable
    expect(in_array($response->status(), [403, 405]))->toBeTrue();
});

test('product name unique per tenant', function () {
    Product::factory()->create([
        'name' => 'Vaso Único',
        'tenant_id' => $this->management->tenant->id,
    ]);

    expect(fn () => Product::factory()->create([
        'name' => 'Vaso Único',
        'tenant_id' => $this->management->tenant->id,
    ]))->toThrow(\Illuminate\Database\UniqueConstraintViolationException::class);
});

test('product creation auto-generates SEO fields via service', function () {
    actingAs($this->management);

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

    // schema_markup must be valid JSON
    $schema = json_decode($product->schema_markup, true);
    expect($schema)->not->toBeNull();
    expect($schema['@type'])->toBe('Product');
    expect($schema['name'])->toBe('Produto SEO Teste');
    expect($schema['offers']['price'])->toBe('99.9');
    expect($schema['offers']['priceCurrency'])->toBe('BRL');
    expect($schema['offers']['availability'])->toBe('https://schema.org/InStock');
    expect($schema['additionalProperty'])->toHaveCount(3); // height, width, weight

    // google_tag_manager must contain dataLayer
    expect($product->google_tag_manager)->toContain('Google Tag Manager');
    expect($product->google_tag_manager)->toContain('product_detail_view');
    expect($product->google_tag_manager)->toContain('Produto SEO Teste');

    // canonical_url is set after slug is generated (via updateQuietly in create)
    expect($product->canonical_url)->toContain('/store/');
    expect($product->canonical_url)->toContain($product->slug);
});

test('product SEO fields are not overwritten when manually provided', function () {
    actingAs($this->management);

    $mockModeration = Mockery::mock(\App\Services\ImageModerationService::class);
    $mockModeration->shouldReceive('moderateUpload')->andReturn(['adult_category_id' => null, 'status' => 'approved', 'category' => 'safe']);
    \Illuminate\Support\Facades\App::instance(\App\Services\ImageModerationService::class, $mockModeration);

    $service = app(\App\Services\ProductService::class);
    $product = $service->create([
        'name' => 'Produto Manual',
        'description' => 'Descrição manual.',
        'sale_price' => 49.90,
        'meta_title' => 'Título Manual Customizado',
        'meta_description' => 'Meta descrição manual.',
        'meta_keywords' => 'custom, manual, keyword',
        'schema_markup' => '{"@type":"Custom"}',
        'google_tag_manager' => '<!-- custom GTM -->',
    ]);

    // Campos fornecidos manualmente devem ser preservados
    expect($product->meta_title)->toBe('Título Manual Customizado');
    expect($product->meta_description)->toBe('Meta descrição manual.');
    expect($product->meta_keywords)->toBe('custom, manual, keyword');
    expect($product->schema_markup)->toBe('{"@type":"Custom"}');
    expect($product->google_tag_manager)->toBe('<!-- custom GTM -->');
});

test('product SEO fields update correctly when name changes', function () {
    actingAs($this->management);

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

    // schema_markup must reflect new name
    expect($product->schema_markup)->toContain('Produto Renomeado');
    expect($product->google_tag_manager)->toContain('Produto Renomeado');

    // Slug is regenerated when name changes, so canonical_url reflects new slug
    expect($product->canonical_url)->toContain($product->slug);
});

test('product seeder generates valid schema markup and GTM', function () {
    // Needs CategoriaSeeder first
    $categoriaSeeder = new \Database\Seeders\CategoriaSeeder();
    $categoriaSeeder->run();

    $seeder = new \Database\Seeders\ProductSeeder();
    $seeder->run();

    $products = Product::withoutGlobalScopes()->get();

    foreach ($products as $product) {
        // schema_markup must be valid JSON
        $schema = json_decode($product->schema_markup, true);
        expect($schema)->not->toBeNull("Schema inválido para produto {$product->name}");
        expect($schema['@type'])->toBe('Product');
        expect($schema['name'])->toBe($product->name);
        expect($schema['offers']['priceCurrency'])->toBe('BRL');

        // google_tag_manager must contain product name and dataLayer
        expect($product->google_tag_manager)->toContain('Google Tag Manager');
        expect($product->google_tag_manager)->toContain('product_detail_view');
        expect($product->google_tag_manager)->toContain($product->name);
    }
});
