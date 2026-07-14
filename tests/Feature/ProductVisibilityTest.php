<?php

use App\Models\Carrier;
use App\Models\CarrierTenantAgreement;
use App\Models\Product;
use App\Models\User;
use App\Services\ProductService;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    Log::spy();

    $this->seller = User::factory()->seller1()->create([
        'email_verified_at' => now(),
    ]);

    $this->tenant = $this->seller->tenant()->create([
        'company_name_encrypted' => makeEncr('Test Co')['encrypted'],
        'company_name_hash'      => makeEncr('Test Co')['hash'],
        'fantasy_name'           => 'Test Co',
        'fantasy_slug'           => 'test-co-' . uniqid(),
        'document'               => '12.345.678/0001-90',
        'city'                   => 'SP',
        'state'                  => 'SP',
        'zipcode'                => '01000-000',
        'active'                 => true,
    ]);

    $this->carrierUser = User::factory()->carrier1()->create([
        'email_verified_at' => now(),
    ]);

    $this->carrier = Carrier::factory()->create([
        'user_id'             => $this->carrierUser->id,
        'fantasy_name'        => 'Express Transportes',
        'is_active'           => true,
        'company_name_encrypted' => makeEncr('Express Ltda')['encrypted'],
        'company_name_hash'     => makeEncr('Express Ltda')['hash'],
        'slug'                => Carrier::generateUniqueSlug('Express Transportes'),
    ]);

    CarrierTenantAgreement::create([
        'tenant_id'  => $this->tenant->id,
        'carrier_id' => $this->carrier->id,
        'status'     => CarrierTenantAgreement::STATUS_ACTIVE,
    ]);

    $this->product = Product::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Produto Visível',
        'slug'      => Product::generateUniqueSlug('Produto Visível', $this->tenant->id),
        'sale_price' => 99.90,
        'is_active' => true,
    ]);

    $this->service = app(ProductService::class);
});

test('product is visible when all 5 criteria are met', function () {
    $products = Product::withoutGlobalScopes()->availableForSale()->get();

    expect($products->pluck('id'))->toContain($this->product->id);
});

test('product is hidden when seller email is not verified', function () {
    $this->seller->update(['email_verified_at' => null]);

    $products = Product::withoutGlobalScopes()->availableForSale()->get();

    expect($products->pluck('id'))->not->toContain($this->product->id);
});

test('product is hidden when carrier email is not verified', function () {
    $this->carrierUser->update(['email_verified_at' => null]);

    $products = Product::withoutGlobalScopes()->availableForSale()->get();

    expect($products->pluck('id'))->not->toContain($this->product->id);
});

test('product is hidden when there is no active carrier agreement', function () {
    CarrierTenantAgreement::where('tenant_id', $this->tenant->id)->delete();

    $products = Product::withoutGlobalScopes()->availableForSale()->get();

    expect($products->pluck('id'))->not->toContain($this->product->id);
});

test('product is hidden when tenant is inactive', function () {
    $this->tenant->update(['active' => false]);

    $products = Product::withoutGlobalScopes()->availableForSale()->get();

    expect($products->pluck('id'))->not->toContain($this->product->id);
});

test('empty store logs diagnostic warnings', function () {
    // Remove transportadora — produto some da vitrine
    CarrierTenantAgreement::where('tenant_id', $this->tenant->id)->delete();

    $this->service->listActiveForStore();

    Log::shouldHaveReceived('warning')->withArgs(function ($message, $context) {
        return str_contains($message, 'transportadora ativa');
    });
});

test('empty store shows generic message in frontend', function () {
    // Remove transportadora — produto some da vitrine
    CarrierTenantAgreement::where('tenant_id', $this->tenant->id)->delete();

    $response = $this->get('/store');

    $response->assertStatus(200);
    $response->assertSee('Nenhum produto disponível no momento.');
    $response->assertSee('verifique os logs do sistema para mais detalhes.');
});

test('changing email invalidates verification', function () {
    $oldVerifiedAt = $this->seller->email_verified_at;

    $this->seller->update(['email' => 'novo@email.com']);

    $this->seller->refresh();
    expect($this->seller->email_verified_at)->toBeNull();
    expect($this->seller->email)->toBe('novo@email.com');
    expect($oldVerifiedAt)->not->toBeNull(); // Estava verificado antes
});