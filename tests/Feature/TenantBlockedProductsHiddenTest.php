<?php

use App\Models\Carrier;
use App\Models\CarrierTenantAgreement;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ProductService;

/**
 * Testa que produtos de tenant bloqueado (active = false) NÃO aparecem na store pública,
 * mas seu campo is_active permanece inalterado.
 */
it('hides products from blocked tenants in the store', function () {
    // Arrange: criar tenant ativo com usuário, transportadora ativa e contrato
    $seller = User::factory()->create([
        'access_level'      => \App\Enums\UserAccessLevel::SELLER_2,
        'email_verified_at' => now(),
    ]);

    $tenant = Tenant::factory()->create([
        'user_id' => $seller->id,
        'active'  => true,
    ]);

    $carrierUser = User::factory()->carrier1()->create([
        'email_verified_at' => now(),
    ]);

    $carrier = Carrier::factory()->create([
        'user_id'             => $carrierUser->id,
        'fantasy_name'        => 'Express Transportes',
        'is_active'           => true,
        'company_name_encrypted' => makeEncr('Express Ltda')['encrypted'],
        'company_name_hash'     => makeEncr('Express Ltda')['hash'],
        'slug'                => Carrier::generateUniqueSlug('Express Transportes'),
    ]);

    CarrierTenantAgreement::create([
        'tenant_id'  => $tenant->id,
        'carrier_id' => $carrier->id,
        'status'     => CarrierTenantAgreement::STATUS_ACTIVE,
    ]);

    $category = Category::create(['name' => 'Decoração', 'slug' => 'decoracao']);

    $product = Product::factory()->create([
        'tenant_id' => $tenant->id,
        'name'      => 'Vaso 3D',
        'slug'      => 'vaso-3d',
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