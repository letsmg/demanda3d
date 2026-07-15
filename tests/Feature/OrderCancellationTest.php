<?php

use App\Models\CartItem;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\User;
use App\Services\CheckoutService;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

beforeEach(function () {
    // Setup: seller + tenant + product + client
    $this->seller = User::factory()->seller1()->create();
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

    $this->product = Product::factory()->create([
        'tenant_id'   => $this->tenant->id,
        'name'        => 'Produto Cancelável',
        'slug'        => 'produto-cancelavel-' . uniqid(),
        'sale_price'  => 99.90,
        'is_active'   => true,
    ]);

    $this->client = Client::factory()->create([
        'tenant_id'            => $this->tenant->id,
        'display_name'         => 'Cliente Cancelador',
        'email'                => 'cancelador_' . uniqid() . '@test.com',
        'address_encrypted'    => makeEncr('Rua Cancelamento')['encrypted'],
        'address_hash'         => makeEncr('Rua Cancelamento')['hash'],
        'number_encrypted'     => makeEncr('999')['encrypted'],
        'number_hash'          => makeEncr('999')['hash'],
        'city_encrypted'       => makeEncr('São Paulo')['encrypted'],
        'city_hash'            => makeEncr('São Paulo')['hash'],
        'state_encrypted'      => makeEncr('SP')['encrypted'],
        'state_hash'           => makeEncr('SP')['hash'],
        'zipcode_encrypted'    => makeEncr('01000-000')['encrypted'],
        'zipcode_hash'         => makeEncr('01000-000')['hash'],
    ]);

    $this->service = app(CheckoutService::class);
});

// ── Helper para criar pedido via checkout ──
function createPaidOrder($client, $product, $deliveredAt = null): Order
{
    CartItem::create([
        'client_id'  => $client->id,
        'product_id' => $product->id,
        'quantity'   => 1,
    ]);

    $order = app(CheckoutService::class)->createOrderFromCart($client, [
        'stripe_session_id' => 'cs_test_' . uniqid(),
    ]);

    $order->update(['status' => 'paid']);

    if ($deliveredAt) {
        $order->update(['delivered_at' => $deliveredAt]);
    }

    return $order;
}

// ═══════════════════════════════════════════════════
// 1. CANCELAMENTO DENTRO DO PRAZO
// ═══════════════════════════════════════════════════
test('client can request return within 7 days of delivery', function () {
    $order = createPaidOrder($this->client, $this->product, now()->subDays(3));

    actingAs($this->client, 'clients');

    $response = post("/perfil/pedidos/{$order->id}/devolucao", [
        'reason' => 'Produto com defeito.',
    ]);

    $response->assertRedirect();

    $return = ReturnRequest::where('order_id', $order->id)->first();
    expect($return)->not->toBeNull();
    expect($return->status)->toBe('requested');
});

// ═══════════════════════════════════════════════════
// 2. CANCELAMENTO FORA DO PRAZO (BLOQUEADO)
// ═══════════════════════════════════════════════════
test('client cannot request return after 7 days of delivery', function () {
    $order = createPaidOrder($this->client, $this->product, now()->subDays(10));

    actingAs($this->client, 'clients');

    $response = post("/perfil/pedidos/{$order->id}/devolucao", [
        'reason' => 'Não quero mais.',
    ]);

    $response->assertRedirect();

    expect(ReturnRequest::where('order_id', $order->id)->exists())->toBeFalse();
});

// ═══════════════════════════════════════════════════
// 3. CANCELAMENTO ANTES DA ENTREGA (PERMITIDO)
// ═══════════════════════════════════════════════════
test('client can request return before delivery', function () {
    // sem delivered_at — ainda não entregue
    $order = createPaidOrder($this->client, $this->product, null);

    actingAs($this->client, 'clients');

    $response = post("/perfil/pedidos/{$order->id}/devolucao", [
        'reason' => 'Arrependimento.',
    ]);

    $response->assertRedirect();

    expect(ReturnRequest::where('order_id', $order->id)->exists())->toBeTrue();
});

// ═══════════════════════════════════════════════════
// 4. BYPASS — OUTRO CLIENTE TENTA CANCELAR
// ═══════════════════════════════════════════════════
test('another client cannot cancel someone elses order', function () {
    $order = createPaidOrder($this->client, $this->product, now()->subDays(1));

    $otherClient = Client::factory()->create([
        'tenant_id' => $this->tenant->id,
        'display_name' => 'Cliente Malicioso',
        'email' => 'malicioso_' . uniqid() . '@test.com',
        'address_encrypted' => makeEncr('Rua Hacker')['encrypted'],
        'address_hash' => makeEncr('Rua Hacker')['hash'],
        'number_encrypted' => makeEncr('666')['encrypted'],
        'number_hash' => makeEncr('666')['hash'],
    ]);

    actingAs($otherClient, 'clients');

    $response = post("/perfil/pedidos/{$order->id}/devolucao", [
        'reason' => 'Cancelamento indevido.',
    ]);

    // Deve retornar 403 — o pedido não pertence a este cliente
    $response->assertForbidden();
});

// ═══════════════════════════════════════════════════
// 5. ORDER MODEL canBeCancelled
// ═══════════════════════════════════════════════════
test('Order canBeCancelled works correctly', function () {
    $order = createPaidOrder($this->client, $this->product, null);
    expect($order->canBeCancelled())->toBeTrue('Pode cancelar antes da entrega');

    $order->update(['delivered_at' => now()->subDays(3)]);
    expect($order->canBeCancelled())->toBeTrue('Pode cancelar 3 dias após entrega');

    $order->update(['delivered_at' => now()->subDays(8)]);
    expect($order->canBeCancelled())->toBeFalse('Não pode cancelar 8 dias após entrega');

    $order->update(['status' => 'shipped', 'delivered_at' => null]);
    expect($order->canBeCancelled())->toBeFalse('Status shipped não permite cancelamento');
});