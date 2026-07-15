<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\CartItem;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderLabel;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    /**
     * Converte os itens do carrinho em um pedido com snapshots imutáveis.
     *
     * Executado dentro de DB::transaction para garantir atomicidade:
     *   - Se qualquer etapa falhar, nada persiste.
     *   - Garante consistência entre Order, OrderItems e OrderLabel.
     *
     * @param Client $client    Cliente autenticado (guard: clients)
     * @param array  $options   Opções do checkout: carrier_id, coupon_code, stripe_session_id
     * @return Order            Pedido criado com itens e etiqueta inicial
     *
     * @throws \RuntimeException Se o carrinho estiver vazio
     */
    public function createOrderFromCart(Client $client, array $options = []): Order
    {
        $cartItems = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes()->with('images');
        }])->where('client_id', $client->id)->get();

        if ($cartItems->isEmpty()) {
            throw new \RuntimeException('O carrinho está vazio.');
        }

        return DB::transaction(function () use ($client, $cartItems, $options) {
            // ── 1. Cria a Order ──────────────────────────────
            $firstTenantId = $cartItems->first()->product->tenant_id ?? 1;

            // Snapshot do endereço de entrega (imutável)
            $addressSnapshot = json_encode([
                'address' => $client->address ?? '',
                'number'  => $client->number ?? '',
                'city'    => $client->city ?? '',
                'state'   => $client->state ?? '',
                'zipcode' => $client->zipcode ?? '',
            ], JSON_UNESCAPED_UNICODE);

            // Snapshot do primeiro produto (nome + preço)
            $firstProduct = $cartItems->first()->product;
            $productName = $firstProduct->name ?? 'Produto';
            $productPrice = (float) ($firstProduct->sale_price ?? 0);

            $order = Order::create([
                'tenant_id'            => $firstTenantId,
                'client_id'            => $client->id,
                'order_date'           => now()->toDateString(),
                'delivery_date'        => now()->addDays(15)->toDateString(),
                'stripe_session_id'    => $options['stripe_session_id'] ?? null,
                'amount_total'         => null,
                'currency'             => $options['currency'] ?? 'brl',
                'status'               => 'pending',
                'snapshot_address'     => $addressSnapshot,
                'snapshot_product_name'=> $productName,
                'snapshot_product_price'=> $productPrice,
            ]);

            // ── 2. Cria OrderItems com snapshot imutável ─────
            $totalAmount = 0;

            foreach ($cartItems as $cartItem) {
                $product = $cartItem->product;

                if (! $product || ! $product->exists) {
                    throw new \RuntimeException("Produto '{$cartItem->product_id}' não encontrado.");
                }

                $unitPrice = (float) $product->sale_price;
                $lineTotal = $unitPrice * $cartItem->quantity;
                $totalAmount += $lineTotal;

                OrderItem::create([
                    'order_id'               => $order->id,
                    'product_id'             => $product->id,
                    'snapshot_product_name'  => $product->name,
                    'snapshot_product_price' => $unitPrice,
                    'quantity'               => $cartItem->quantity,
                ]);
            }

            // Atualiza o total calculado na Order
            $order->update(['amount_total' => $totalAmount]);

            // ── 3. Gera OrderLabel com snapshot do destinatário
            //
            // Captura o nome e endereço EXATOS do cliente neste momento.
            // Qualquer alteração futura no perfil do cliente NÃO afeta
            // etiquetas já geradas — a reimpressão lê daqui, não do Client.
            $this->createInitialLabel($order, $client, $options['carrier_id'] ?? null);

            // ── 4. Limpa o carrinho ─────────────────────────
            CartItem::where('client_id', $client->id)->delete();

            return $order;
        });
    }

    /**
     * Cria o registro inicial de etiqueta (order_label) com snapshot
     * imutável do nome e endereço do destinatário no momento da compra.
     *
     * O campo recipient_address é armazenado como JSON estruturado para
     * facilitar a formatação na impressão da etiqueta.
     */
    private function createInitialLabel(Order $order, Client $client, ?int $carrierId = null): OrderLabel
    {
        // Monta o endereço completo estruturado
        $addressSnapshot = json_encode([
            'address'  => $client->address ?? '',
            'number'   => $client->number ?? '',
            'district' => $client->district ?? '',
            'city'     => $client->city ?? '',
            'state'    => $client->state ?? '',
            'zipcode'  => $client->zipcode ?? '',
        ], JSON_UNESCAPED_UNICODE);

        return OrderLabel::create([
            'order_id'          => $order->id,
            'carrier_id'        => $carrierId,
            'tenant_id'         => $order->tenant_id,
            'status'            => 'pending',
            'recipient_name'    => $client->display_name ?? $client->first_name ?? 'Destinatário',
            'recipient_address' => $addressSnapshot,
        ]);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.