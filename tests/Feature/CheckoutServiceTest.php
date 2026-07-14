<?php

use App\Models\CartItem;
use App\Models\Client;
use App\Models\OrderItem;
use App\Models\OrderLabel;
use App\Models\Product;
use App\Models\User;
use App\Services\CheckoutService;


beforeEach(function () {
    $this->seller = User::factory()->seller1()->create();
    $this->tenant = $this->seller->tenant()->create([
        'company_name_encrypted' => makeEncr('Test Co')['encrypted'],
        'company_name_hash'      => makeEncr('Test Co')['hash'],
        'fantasy_name'           => 'Test Co',
        'fantasy_slug'           => 'test-co',
        'document'               => '12.345.678/0001-90',
        'city'                   => 'SP',
        'state'                  => 'SP',
        'zipcode'                => '01000-000',
        'active'                 => true,
    ]);

    $this->product = Product::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Produto Teste',
        'slug'      => 'produto-teste',
        'sale_price' => 99.90,
        'is_active' => true,
    ]);

    $this->client = Client::create([
        'tenant_id'           => $this->tenant->id,
        'display_name'        => 'Cliente Teste',
        'first_name_encrypted' => makeEncr('Cliente')['encrypted'],
        'first_name_hash'      => makeEncr('Cliente')['hash'],
        'last_name_encrypted'  => makeEncr('Teste')['encrypted'],
        'last_name_hash'       => makeEncr('Teste')['hash'],
        'email'               => 'cliente@test.com',
        'address_encrypted'   => makeEncr('Rua Teste')['encrypted'],
        'address_hash'        => makeEncr('Rua Teste')['hash'],
        'number_encrypted'    => makeEncr('100')['encrypted'],
        'number_hash'         => makeEncr('100')['hash'],
        'city_encrypted'      => makeEncr('São Paulo')['encrypted'],
        'city_hash'           => makeEncr('São Paulo')['hash'],
        'state_encrypted'     => makeEncr('SP')['encrypted'],
        'state_hash'          => makeEncr('SP')['hash'],
        'zipcode_encrypted'   => makeEncr('01000-000')['encrypted'],
        'zipcode_hash'        => makeEncr('01000-000')['hash'],
    ]);

    $this->service = app(CheckoutService::class);
});

test('checkout creates order with immutable snapshots', function () {
    CartItem::create([
        'client_id'  => $this->client->id,
        'product_id' => $this->product->id,
        'quantity'   => 2,
    ]);

    $order = $this->service->createOrderFromCart($this->client, []);

    expect($order)->not->toBeNull();
    expect($order->status)->toBe('pending');
    expect($order->client_id)->toBe($this->client->id);
    expect($order->tenant_id)->toBe($this->tenant->id);

    // Deve ter 1 order_item com snapshot imutável
    $items = $order->items;
    expect($items)->toHaveCount(1);
    expect($items->first()->snapshot_product_name)->toBe('Produto Teste');
    expect((float) $items->first()->snapshot_product_price)->toBe(99.90);
    expect($items->first()->quantity)->toBe(2);

    // Total deve ser 199.80
    expect((float) $order->amount_total)->toBe(199.80);
});

test('checkout creates order label with recipient snapshot', function () {
    CartItem::create([
        'client_id'  => $this->client->id,
        'product_id' => $this->product->id,
        'quantity'   => 1,
    ]);

    $order = $this->service->createOrderFromCart($this->client, []);

    $labels = $order->labels;
    expect($labels)->toHaveCount(1);
    expect($labels->first()->recipient_name)->toBe('Cliente Teste');
    expect($labels->first()->status)->toBe('pending');

    // recipient_address deve ser JSON válido
    $addr = json_decode($labels->first()->recipient_address, true);
    expect($addr)->toBeArray();
    expect($addr['address'])->toBe('Rua Teste');
    expect($addr['zipcode'])->toBe('01000-000');
});

test('checkout clears cart after success', function () {
    CartItem::create([
        'client_id'  => $this->client->id,
        'product_id' => $this->product->id,
        'quantity'   => 1,
    ]);

    expect(CartItem::where('client_id', $this->client->id)->count())->toBe(1);

    $this->service->createOrderFromCart($this->client, []);

    expect(CartItem::where('client_id', $this->client->id)->count())->toBe(0);
});

test('checkout throws exception for empty cart', function () {
    $this->service->createOrderFromCart($this->client, []);
})->throws(\RuntimeException::class, 'carrinho está vazio');

test('order item snapshots survive product changes', function () {
    CartItem::create([
        'client_id'  => $this->client->id,
        'product_id' => $this->product->id,
        'quantity'   => 1,
    ]);

    $order = $this->service->createOrderFromCart($this->client, []);

    // Altera o produto após a compra
    $this->product->update(['name' => 'Produto Alterado', 'sale_price' => 199.90]);

    // Snapshot deve permanecer inalterado
    $item = $order->items->first();
    expect($item->snapshot_product_name)->toBe('Produto Teste');
    expect((float) $item->snapshot_product_price)->toBe(99.90);
});

test('order item survives product soft delete', function () {
    CartItem::create([
        'client_id'  => $this->client->id,
        'product_id' => $this->product->id,
        'quantity'   => 1,
    ]);

    $order = $this->service->createOrderFromCart($this->client, []);

    // Soft delete do produto
    $this->product->delete();

    $item = OrderItem::find($order->items->first()->id);
    expect($item->snapshot_product_name)->toBe('Produto Teste');

    // Soft delete (UPDATE em deleted_at) não aciona ON DELETE SET NULL da FK.
    // O registro do produto ainda existe, mas está marcado como deletado.
    // O snapshot imutável no order_item garante que o histórico de pedido permanece correto.
    expect($this->product->fresh())->not->toBeNull();
    expect($this->product->fresh()->trashed())->toBeTrue();
});
