<?php

use App\Models\Category;
use App\Models\Carrier;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VendorCarrier;
use App\Services\ProductService;


/**
 * Testa que produtos de tenant bloqueado (active = false) NÃO aparecem na store pública,
 * mas seu campo is_active permanece inalterado.
 */
it('hides products from blocked tenants in the store', function () {
    // Arrange: criar tenant ativo com usuário e transportadora aprovada
    $user = User::factory()->create([
        'access_level' => \App\Enums\UserAccessLevel::SELLER_2,
    ]);

    $tenant = Tenant::factory()->create([
        'user_id' => $user->id,
        'active' => true,
    ]);

    $carrier = Carrier::factory()->withUser()->create();
    VendorCarrier::create([
        'user_id' => $user->id,
        'carrier_id' => $carrier->id,
        'status' => 'approved',
    ]);

    $category = Category::create(['name' => 'Decoração', 'slug' => 'decoracao']);

    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Vaso 3D',
        'slug' => 'vaso-3d',
        'is_active' => true,
        'sale_price' => 49.90,
        'moderation_status' => 'approved',
    ]);

    $product->categories()->attach($category->id);

    /** @var ProductService $service */
    $service = app(ProductService::class);

    // Act 1: Tenant ativo → produto aparece
    $results = $service->listActiveForStore(['search' => 'Vaso 3D']);
    $found = $results->contains('id', $product->id);
    expect($found)->toBeTrue('Produto deve aparecer quando tenant está ativo');

    // Act 2: Bloquear tenant (active = false)
    $tenant->update(['active' => false]);

    // Limpar cache antes de verificar
    \Illuminate\Support\Facades\Cache::flush();

    // Act 3: Produto NÃO aparece
    $results = $service->listActiveForStore(['search' => 'Vaso 3D']);
    $found = $results->contains('id', $product->id);
    expect($found)->toBeFalse('Produto NÃO deve aparecer quando tenant está bloqueado');

    // Assert: is_active do produto permanece true
    $product->refresh();
    expect($product->is_active)->toBeTrue('is_active do produto deve permanecer true mesmo com tenant bloqueado');

    // Act 4: Reativar tenant → produto volta
    $tenant->update(['active' => true]);
    \Illuminate\Support\Facades\Cache::flush();

    $results = $service->listActiveForStore(['search' => 'Vaso 3D']);
    $found = $results->contains('id', $product->id);
    expect($found)->toBeTrue('Produto deve voltar a aparecer quando tenant é reativado');
});

it('returns 404 when accessing a blocked tenant profile', function () {
    $user = User::factory()->create([
        'access_level' => \App\Enums\UserAccessLevel::SELLER_2,
    ]);

    Tenant::factory()->create([
        'user_id' => $user->id,
        'active' => false,
        'fantasy_slug' => 'loja-bloqueada',
    ]);

    $response = $this->get('/tenant/loja-bloqueada');
    $response->assertNotFound();
});

it('returns 200 when accessing an active tenant profile', function () {
    $user = User::factory()->create([
        'access_level' => \App\Enums\UserAccessLevel::SELLER_2,
    ]);

    Tenant::factory()->create([
        'user_id' => $user->id,
        'active' => true,
        'fantasy_slug' => 'loja-ativa',
    ]);

    $response = $this->get('/tenant/loja-ativa');
    $response->assertOk();
});