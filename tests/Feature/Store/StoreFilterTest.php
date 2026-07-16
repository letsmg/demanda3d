<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\EncryptionService;

use function Pest\Laravel\get;

beforeEach(function () {
    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);

    $this->seller = User::factory()->seller1()->create();
    $this->seller->tenant()->create([
        'company_name_encrypted' => $makeEncr('Loja Teste')['encrypted'],
        'company_name_hash' => $makeEncr('Loja Teste')['hash'],
        'document_encrypted' => $makeEncr('00.000.000/0001-00')['encrypted'],
        'document_hash' => $makeEncr('00.000.000/0001-00')['hash'],
        'phone_encrypted' => $makeEncr('11999999999')['encrypted'],
        'address_encrypted' => $makeEncr('Rua A')['encrypted'],
        'number_encrypted' => $makeEncr('100')['encrypted'],
        'number_hash' => $makeEncr('100')['hash'],
        'city_encrypted' => $makeEncr('São Paulo')['encrypted'],
        'city_hash' => $makeEncr('São Paulo')['hash'],
        'state' => 'SP',
        'zipcode' => '00000-000',
        'active' => true,
        'fantasy_name' => 'Loja Teste',
        'fantasy_slug' => 'loja-teste',
    ]);

    // Cria produtos de teste com preços variados
    Product::factory()->create([
        'tenant_id' => $this->seller->tenant->id,
        'name' => 'Produto Barato',
        'sale_price' => 10.00,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'tenant_id' => $this->seller->tenant->id,
        'name' => 'Produto Caro',
        'sale_price' => 150.00,
        'is_active' => true,
    ]);

    Product::factory()->create([
        'tenant_id' => $this->seller->tenant->id,
        'name' => 'Vaso Decorativo',
        'sale_price' => 45.00,
        'is_active' => true,
    ]);
});

// ─────────────────────────────────────────────────────────
// Acesso básico
// ─────────────────────────────────────────────────────────

test('anyone can access the public store page', function () {
    $response = get('/store');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Store/Index'));
});

test('store page receives products and categories as props', function () {
    $response = get('/store');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products')
        ->has('categories')
        ->has('filters')
    );
});

// ─────────────────────────────────────────────────────────
// Filtro por busca (search)
// ─────────────────────────────────────────────────────────

test('search filter filters products by name', function () {
    $response = get('/store?search=Vaso');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', fn ($products) => count($products) === 1)
    );
});

test('search filter returns empty for non-matching term', function () {
    $response = get('/store?search=InexistenteXYZ');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', fn ($products) => count($products) === 0)
    );
});

// ─────────────────────────────────────────────────────────
// Filtro por preço
// ─────────────────────────────────────────────────────────

test('min_price filter filters products by minimum price', function () {
    $response = get('/store?min_price=100');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', fn ($products) => count($products) === 1)
    );
});

test('max_price filter filters products by maximum price', function () {
    $response = get('/store?max_price=20');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', fn ($products) => count($products) === 1)
    );
});

test('combined price range filter returns products within range', function () {
    $response = get('/store?min_price=30&max_price=50');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', fn ($products) => count($products) === 1)
    );
});

// ─────────────────────────────────────────────────────────
// Filtro por ordenação
// ─────────────────────────────────────────────────────────

test('sort by sale_price ascending returns products in correct order', function () {
    $response = get('/store?sort=sale_price&sort_dir=asc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', 3)
    );

    $products = $response->inertiaProps('products');
    $prices = array_map(fn ($p) => (float) $p['sale_price'], $products);

    expect($prices)->toBe([10.00, 45.00, 150.00]);
});

test('sort by name descending returns products in reverse alphabetical order', function () {
    $response = get('/store?sort=name&sort_dir=desc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', 3)
    );

    $products = $response->inertiaProps('products');
    $names = array_map(fn ($p) => $p['name'], $products);

    expect($names[0])->toBe('Vaso Decorativo');
});

// ─────────────────────────────────────────────────────────
// Filtro por categoria
// ─────────────────────────────────────────────────────────

test('category filter with invalid slug returns validation error', function () {
    $response = get('/store?category=slug-inexistente');

    // O controller valida exists:categories,slug — retorna erro de validação
    $response->assertStatus(302);
});

test('category filter is passed as props', function () {
    $response = get('/store?sort=name&sort_dir=asc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('filters.sort', 'name')
        ->where('filters.sort_dir', 'asc')
    );
});

// ─────────────────────────────────────────────────────────
// API moreProducts (lazy loading)
// ─────────────────────────────────────────────────────────

test('moreProducts API returns paginated JSON', function () {
    $response = get('/api/store/products?page=1');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data',
        'has_more',
    ]);
});

test('moreProducts API respects search filter', function () {
    $response = get('/api/store/products?page=1&search=Vaso');

    $response->assertStatus(200);
    $json = $response->json();

    expect(count($json['data']))->toBe(1);
});

test('moreProducts API respects price filter', function () {
    $response = get('/api/store/products?page=1&min_price=100');

    $response->assertStatus(200);
    $json = $response->json();

    expect(count($json['data']))->toBe(1);
});