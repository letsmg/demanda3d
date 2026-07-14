<?php

use App\Models\Carrier;
use App\Models\CarrierTenantAgreement;
use App\Models\User;
use App\Services\SplitPayService;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

beforeEach(function () {
    $this->seller = User::factory()->seller1()->create(['email_verified_at' => now()]);
    $this->seller2 = User::factory()->seller2()->create(['email_verified_at' => now()]);

    $this->tenant = $this->seller->tenant()->create([
        'company_name_encrypted' => makeEncr('Loja Teste')['encrypted'],
        'company_name_hash'      => makeEncr('Loja Teste')['hash'],
        'fantasy_name'           => 'Loja Teste',
        'fantasy_slug'           => 'loja-teste-' . uniqid(),
        'document'               => '12.345.678/0001-90',
        'city'                   => 'SP', 'state' => 'SP', 'zipcode' => '01000-000', 'active' => true,
    ]);

    $this->carrierUser = User::factory()->carrier1()->create(['email_verified_at' => now()]);
    $this->carrier = Carrier::factory()->create([
        'user_id'               => $this->carrierUser->id,
        'fantasy_name'          => 'Transportadora Teste',
        'is_active'             => true,
        'company_name_encrypted' => makeEncr('Transp Ltda')['encrypted'],
        'company_name_hash'     => makeEncr('Transp Ltda')['hash'],
        'slug'                  => Carrier::generateUniqueSlug('Transportadora Teste'),
    ]);

    $this->agreement = CarrierTenantAgreement::create([
        'tenant_id'  => $this->tenant->id,
        'carrier_id' => $this->carrier->id,
        'status'     => CarrierTenantAgreement::STATUS_ACTIVE,
    ]);
});

// ─── Segurança: Bloqueio mútuo ──────────────────────────────

test('carrier can block a seller', function () {
    $this->carrier->doesCoverCep = fn () => true; // mock

    $this->agreement->blockBy('carrier');

    expect($this->agreement->fresh()->isBlocked())->toBeTrue();
    expect($this->agreement->fresh()->blockedBy())->toBe('carrier');
});

test('seller can block a carrier', function () {
    $this->agreement->blockBy('seller');

    expect($this->agreement->fresh()->isBlocked())->toBeTrue();
    expect($this->agreement->fresh()->blockedBy())->toBe('seller');
});

test('blocked agreement can be unblocked', function () {
    $this->agreement->blockBy('seller');
    $this->agreement->fresh()->unblock();

    expect($this->agreement->fresh()->isBlocked())->toBeFalse();
    expect($this->agreement->fresh()->isActive())->toBeTrue();
    expect($this->agreement->fresh()->blockedBy())->toBeNull();
});

test('seller2 does not have financial permissions to manage carriers', function () {
    // SELLER_2 não tem acesso financeiro (canAccessFinancials = false)
    expect($this->seller2->access_level->canAccessFinancials())->toBeFalse();
    expect($this->seller2->canAccessFinancials())->toBeFalse();

    // Apenas ADMIN e SELLER_1 podem
    expect($this->seller->access_level->canAccessFinancials())->toBeTrue();
});

// ─── Split Pay: Divisão de valores ──────────────────────────

test('split pay divides order correctly between seller, carrier and platform', function () {
    $product = Product::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Produto Teste',
        'slug'      => Product::generateUniqueSlug('Produto Teste', $this->tenant->id),
        'sale_price' => 100.00,
        'is_active' => true,
    ]);

    $order = Order::create([
        'tenant_id'     => $this->tenant->id,
        'client_id'     => 1,
        'order_date'    => now()->toDateString(),
        'delivery_date' => now()->addDays(15)->toDateString(),
        'amount_total'  => 150.00, // 100 produto + 50 frete
        'status'        => 'pending',
    ]);

    OrderItem::create([
        'order_id'               => $order->id,
        'product_id'             => $product->id,
        'snapshot_product_name'  => $product->name,
        'snapshot_product_price' => 100.00,
        'quantity'               => 1,
    ]);

    $order->load('items');

    $service = app(SplitPayService::class);
    $split = $service->calculateSplit($order);

    expect($split['seller'])->toBe(90.00);   // 100 - 10%
    expect($split['platform'])->toBe(10.00);  // 10%
    expect($split['carrier'])->toBe(50.00);   // frete integral
    expect($split['total'])->toBe(150.00);
});