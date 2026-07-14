<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;


beforeEach(function () {
    $seller = User::factory()->seller1()->create();
    $this->tenant = $seller->tenant()->create([
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

    $this->client = \App\Models\Client::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);

    $this->product = Product::create([
        'tenant_id' => $this->tenant->id,
        'name'      => 'Produto Snapshot',
        'slug'      => 'produto-snapshot',
        'sale_price' => 149.90,
        'is_active' => true,
    ]);

    $this->order = Order::create([
        'tenant_id'     => $this->tenant->id,
        'client_id'     => $this->client->id,
        'order_date'    => now()->toDateString(),
        'delivery_date' => now()->addDays(15)->toDateString(),
        'status'        => 'pending',
    ]);
});

test('order item snapshot captures product data at creation time', function () {
    $item = OrderItem::create([
        'order_id'               => $this->order->id,
        'product_id'             => $this->product->id,
        'snapshot_product_name'  => $this->product->name,
        'snapshot_product_price' => $this->product->sale_price,
        'quantity'               => 3,
    ]);

    expect($item->snapshot_product_name)->toBe('Produto Snapshot');
    expect((float) $item->snapshot_product_price)->toBe(149.90);
    expect($item->quantity)->toBe(3);
});

test('order item subtotal is calculated correctly', function () {
    $item = OrderItem::create([
        'order_id'               => $this->order->id,
        'product_id'             => $this->product->id,
        'snapshot_product_name'  => 'Test',
        'snapshot_product_price' => 50.00,
        'quantity'               => 4,
    ]);

    expect($item->subtotal())->toBe(200.00);
});

test('order calculateTotal sums all items', function () {
    OrderItem::create([
        'order_id'               => $this->order->id,
        'product_id'             => $this->product->id,
        'snapshot_product_name'  => 'Item 1',
        'snapshot_product_price' => 100.00,
        'quantity'               => 2,
    ]);

    OrderItem::create([
        'order_id'               => $this->order->id,
        'product_id'             => $this->product->id,
        'snapshot_product_name'  => 'Item 2',
        'snapshot_product_price' => 50.00,
        'quantity'               => 1,
    ]);

    $this->order->load('items');
    expect($this->order->calculateTotal())->toBe(250.00);
});