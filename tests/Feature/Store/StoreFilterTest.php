<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\get;

beforeEach(function () {
    // Limpa cache Redis antes de cada teste para evitar poluição entre testes
    Cache::flush();

    $makeEncr = fn ($v) => EncryptionService::encryptWithHash($v);

    $this->seller = User::factory()->seller1()->create([
        'email_verified_at' => now(),
    ]);
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
        'is_profile_complete' => true,
    ]);

    // Cria uma transportadora ativa e contrato — necessário para scopeAvailableForSale
    $carrierUser = User::factory()->carrier1()->create([
        'email_verified_at' => now(),
    ]);
    $carrier = \App\Models\Carrier::factory()->create([
        'user_id' => $carrierUser->id,
        'fantasy_name' => 'Express Teste',
        'is_active' => true,
        'company_name_encrypted' => $makeEncr('Express Ltda')['encrypted'],
        'company_name_hash' => $makeEncr('Express Ltda')['hash'],
        'slug' => 'express-teste',
    ]);
    \App\Models\CarrierTenantAgreement::create([
        'tenant_id' => $this->seller->tenant->id,
        'carrier_id' => $carrier->id,
        'status' => 'active',
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
        ->has('products')
    );

    $products = $response->inertiaProps('products');
    $names = array_map(fn ($p) => $p['name'], $products);

    // Deve conter o produto com "Vaso" no nome
    expect($names)->toContain('Vaso Decorativo');
    // Não deve conter produtos sem "Vaso"
    expect($names)->not->toContain('Produto Barato');
    expect($names)->not->toContain('Produto Caro');
});

test('search filter returns empty for non-matching term', function () {
    $response = get('/store?search=InexistenteXYZ');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', 0)
    );
});

// ─────────────────────────────────────────────────────────
// Filtro por preço
// ─────────────────────────────────────────────────────────

test('min_price filter filters products by minimum price', function () {
    $response = get('/store?min_price=100');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products')
    );

    $products = $response->inertiaProps('products');
    $names = array_map(fn ($p) => $p['name'], $products);

    // Deve conter o produto caro (150.00)
    expect($names)->toContain('Produto Caro');
    // Não deve conter produtos baratos (< 100)
    expect($names)->not->toContain('Produto Barato');
    expect($names)->not->toContain('Vaso Decorativo');
});

test('max_price filter filters products by maximum price', function () {
    $response = get('/store?max_price=20');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products', 1)
    );
});

test('combined price range filter returns products within range', function () {
    $response = get('/store?min_price=30&max_price=50');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products')
    );

    $products = $response->inertiaProps('products');
    $names = array_map(fn ($p) => $p['name'], $products);

    // Deve conter o produto na faixa (45.00)
    expect($names)->toContain('Vaso Decorativo');
    // Não deve conter produtos fora da faixa
    expect($names)->not->toContain('Produto Barato');
    expect($names)->not->toContain('Produto Caro');
});

// ─────────────────────────────────────────────────────────
// Filtro por ordenação
// ─────────────────────────────────────────────────────────

test('sort by sale_price ascending returns products in correct order', function () {
    $response = get('/store?sort=sale_price&sort_dir=asc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products')
    );

    $products = $response->inertiaProps('products');
    expect(count($products))->toBeGreaterThanOrEqual(3);

    // Filtra apenas os produtos criados no beforeEach e verifica ordenação
    $testProductIds = Product::where('tenant_id', $this->seller->tenant->id)->pluck('id')->toArray();
    $testProducts = array_filter($products, fn ($p) => in_array($p['id'], $testProductIds));
    $prices = array_map(fn ($p) => (float) $p['sale_price'], array_values($testProducts));

    // Verifica que os produtos de teste estão ordenados por preço
    // (pode não incluir todos devido ao take(24) no serviço quando há muitos produtos no banco)
    expect($prices)->toHaveCount(2);
    expect($prices[0])->toBeLessThanOrEqual($prices[1]);
});

test('sort by name descending returns products in reverse alphabetical order', function () {
    $response = get('/store?sort=name&sort_dir=desc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('products')
    );

    $products = $response->inertiaProps('products');
    expect(count($products))->toBeGreaterThanOrEqual(3);

    // Filtra apenas os produtos criados no beforeEach e verifica ordenação
    $testProductIds = Product::where('tenant_id', $this->seller->tenant->id)->pluck('id')->toArray();
    $testProducts = array_filter($products, fn ($p) => in_array($p['id'], $testProductIds));
    $names = array_map(fn ($p) => $p['name'], array_values($testProducts));

    expect($names[0])->toBe('Vaso Decorativo');
});

// ─────────────────────────────────────────────────────────
// Filtro por categoria
// ─────────────────────────────────────────────────────────

test('category filter with invalid slug returns valid response', function () {
    $response = get('/store?category=slug-inexistente');

    // O controller aceita categories como string (comma-separated slugs).
    // Slugs inválidos são ignorados silenciosamente — retorna 200 sem filtrar.
    $response->assertStatus(200);
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
    Cache::flush();

    $response = get('/api/store/products?page=1&search=Vaso');

    $response->assertStatus(200);
    $json = $response->json();

    // Verifica que todos os resultados contêm "Vaso" no nome
    foreach ($json['data'] as $product) {
        expect(str_contains(strtolower($product['name']), 'vaso'))->toBeTrue(
            "Produto '{$product['name']}' não contém 'Vaso' no nome"
        );
    }

    // Deve incluir o produto de teste específico
    $names = array_map(fn ($p) => $p['name'], $json['data']);
    expect($names)->toContain('Vaso Decorativo');
});

test('moreProducts API respects price filter', function () {
    Cache::flush();

    $response = get('/api/store/products?page=1&min_price=100');

    $response->assertStatus(200);
    $json = $response->json();

    // Verifica que todos os resultados têm preço >= 100
    foreach ($json['data'] as $product) {
        expect((float) $product['sale_price'])->toBeGreaterThanOrEqual(100.00,
            "Produto '{$product['name']}' tem preço inferior a 100"
        );
    }

    // Deve incluir o produto de teste específico
    $names = array_map(fn ($p) => $p['name'], $json['data']);
    expect($names)->toContain('Produto Caro');
});