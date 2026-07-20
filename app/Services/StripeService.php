<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Integração com Stripe — Checkout Sessions, Connect e Webhooks.
 *
 * Responsável por:
 *  - Criar sessões de checkout com split de pagamento (Connect)
 *  - Validar assinaturas de webhooks
 *  - Consultar status de PaymentIntents
 *  - Processar webhooks de conclusão de checkout
 */
class StripeService
{
    private readonly StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Cria uma Checkout Session no Stripe para o pedido.
     *
     * Configura o repasse (transfer_data) para a conta Connect do vendedor
     * e retém a comissão da plataforma (application_fee).
     *
     * @param  Order  $order   Pedido com items e split já calculados
     * @param  array  $metadata Dados adicionais para o metadata da sessão
     * @return string URL da Checkout Session para redirecionar o cliente
     *
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function createCheckoutSession(Order $order, array $metadata = []): string
    {
        $lineItems = [];

        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => strtolower($order->currency ?? 'brl'),
                    'product_data' => [
                        'name' => $item->product_name ?? 'Produto #' . $item->product_id,
                    ],
                    'unit_amount' => (int) round($item->unit_price * 100),
                ],
                'quantity' => $item->quantity,
            ];
        }

        $sessionConfig = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => route('checkout.success', ['session_id' => '{CHECKOUT_SESSION_ID}']),
            'cancel_url' => route('checkout.cancel'),
            'client_reference_id' => (string) $order->id,
            'metadata' => array_merge([
                'order_id' => (string) $order->id,
                'tenant_id' => (string) $order->tenant_id,
                'client_id' => (string) $order->client_id,
            ], $metadata),
        ];

        // Se o vendedor tem conta Connect, configura application_fee e transfer_data
        $sellerStripeAccountId = $order->tenant->stripe_connect_id ?? null;

        if ($sellerStripeAccountId) {
            // Calcula o split via SplitPayService
            $split = app(SplitPayService::class)->calculateSplit($order);

            $sessionConfig['payment_intent_data'] = [
                'application_fee_amount' => (int) round($split['platform'] * 100),
                'transfer_data' => [
                    'destination' => $sellerStripeAccountId,
                ],
                'metadata' => [
                    'seller_amount' => (string) $split['seller'],
                    'carrier_amount' => (string) $split['carrier'],
                    'platform_fee' => (string) $split['platform'],
                ],
            ];
        }

        $session = $this->stripe->checkout->sessions->create($sessionConfig);

        // Atualiza o pedido com o ID da sessão
        $order->update([
            'stripe_session_id' => $session->id,
            'status' => 'pending_payment',
        ]);

        return $session->url;
    }

    /**
     * Valida e constrói o evento de webhook a partir do payload bruto e assinatura.
     *
     * @param  string  $payload   Corpo bruto da requisição (json)
     * @param  string  $signature Cabeçalho Stripe-Signature
     * @return \Stripe\Event
     *
     * @throws SignatureVerificationException
     */
    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        $secret = config('services.stripe.webhook_secret');

        return Webhook::constructEvent($payload, $signature, $secret);
    }

    /**
     * Recupera um PaymentIntent pelo ID.
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Processa o evento checkout.session.completed.
     *
     * Atualiza o pedido com os dados do PaymentIntent, calcula splits
     * e registra os valores de comissão, vendedor e transportadora.
     *
     * @param  \Stripe\Event  $event
     * @return Order|null
     */
    public function handleCheckoutCompleted(\Stripe\Event $event): ?Order
    {
        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;

        if (! $orderId) {
            Log::warning('Stripe webhook: checkout.session.completed sem order_id.', [
                'stripe_event_id' => $event->id,
                'session_id' => $session->id,
            ]);

            return null;
        }

        $order = Order::find($orderId);

        if (! $order) {
            Log::warning('Stripe webhook: pedido não encontrado.', [
                'order_id' => $orderId,
                'stripe_event_id' => $event->id,
            ]);

            return null;
        }

        // Evita reprocessamento: se já foi processado, pula
        if ($order->status === 'paid' && $order->stripe_payment_intent_id) {
            return $order;
        }

        $paymentIntentId = $session->payment_intent ?? null;

        // Calcula splits (se já não foram salvos)
        if (! $order->platform_fee_amount && ! $order->seller_amount) {
            $split = app(SplitPayService::class)->calculateSplit($order);

            $order->fill([
                'platform_fee_amount' => $split['platform'],
                'seller_amount' => $split['seller'],
                'carrier_amount' => $split['carrier'],
            ]);
        }

        $order->fill([
            'stripe_payment_intent_id' => $paymentIntentId,
            'payment_split_status' => 'processed',
            'status' => 'paid',
        ])->save();

        Log::info('Pedido marcado como pago via Stripe webhook.', [
            'order_id' => $order->id,
            'stripe_payment_intent_id' => $paymentIntentId,
            'amount_total' => $order->amount_total,
        ]);

        return $order;
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.