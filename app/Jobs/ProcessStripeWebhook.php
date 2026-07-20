<?php

namespace App\Jobs;

use App\Models\StripeWebhookLog;
use App\Services\StripeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\Event;

class ProcessStripeWebhook implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $stripeEventId,
        public string $eventType,
    ) {}

    /**
     * Execute the job.
     *
     * Busca o evento pelo ID no Stripe API para garantir integridade,
     * processa conforme o tipo de evento e atualiza o log de webhook.
     *
     * Idempotente: verifica status do webhook_log antes de processar.
     */
    public function handle(StripeService $stripeService): void
    {
        $webhookLog = StripeWebhookLog::where('stripe_event_id', $this->stripeEventId)->first();

        if (! $webhookLog) {
            Log::warning('ProcessStripeWebhook: log não encontrado.', [
                'stripe_event_id' => $this->stripeEventId,
            ]);

            return;
        }

        // Idempotência: se já foi processado, não reprocessa
        if (in_array($webhookLog->status, ['processed', 'skipped'], true)) {
            return;
        }

        try {
            // Busca o evento no Stripe para garantir que é autêntico
            $event = \Stripe\Event::retrieve($this->stripeEventId);

            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutCompleted($event, $stripeService, $webhookLog),
                'checkout.session.expired' => $this->handleCheckoutExpired($event, $webhookLog),
                default => $this->skipUnsupportedEvent($webhookLog, $event->type),
            };
        } catch (ApiErrorException $e) {
            Log::error('ProcessStripeWebhook: erro na API do Stripe.', [
                'stripe_event_id' => $this->stripeEventId,
                'error' => $e->getMessage(),
            ]);

            $webhookLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessStripeWebhook: erro inesperado.', [
                'stripe_event_id' => $this->stripeEventId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $webhookLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Re-throw para que o worker possa retentar conforme configurado
            throw $e;
        }
    }

    /**
     * Processa o evento checkout.session.completed.
     *
     * Atualiza o pedido com status 'paid', calcula splits e registra valores.
     */
    private function handleCheckoutCompleted(
        Event $event,
        StripeService $stripeService,
        StripeWebhookLog $webhookLog,
    ): void {
        $order = $stripeService->handleCheckoutCompleted($event);

        $webhookLog->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);

        if ($order) {
            Log::info('Checkout concluído e pedido atualizado.', [
                'order_id' => $order->id,
                'stripe_event_id' => $this->stripeEventId,
            ]);
        }
    }

    /**
     * Processa o evento checkout.session.expired.
     *
     * Marca o pedido como 'expired' para que o cliente saiba que a sessão expirou.
     */
    private function handleCheckoutExpired(Event $event, StripeWebhookLog $webhookLog): void
    {
        $session = $event->data->object;
        $orderId = $session->metadata->order_id ?? $session->client_reference_id ?? null;

        if ($orderId) {
            $order = \App\Models\Order::find($orderId);

            if ($order && $order->status === 'pending_payment') {
                $order->update(['status' => 'expired']);
            }
        }

        $webhookLog->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Marca eventos não suportados como 'skipped' sem erro.
     */
    private function skipUnsupportedEvent(StripeWebhookLog $webhookLog, string $type): void
    {
        $webhookLog->update([
            'status' => 'skipped',
            'processed_at' => now(),
            'error_message' => "Event type '{$type}' is not supported by this handler.",
        ]);

        Log::info('Stripe webhook ignorado (tipo não suportado).', [
            'event_type' => $type,
            'stripe_event_id' => $this->stripeEventId,
        ]);
    }
}