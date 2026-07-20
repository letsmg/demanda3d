<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStripeWebhook;
use App\Models\StripeWebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    /**
     * POST /stripe/webhook
     *
     * Recebe o webhook do Stripe, valida a assinatura, registra o log
     * e despacha o job para processamento assíncrono via RabbitMQ.
     * Retorna HTTP 200 imediatamente para o Stripe não reenviar o evento.
     */
    public function __invoke(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! $signature) {
            Log::warning('Stripe webhook recebido sem assinatura.');
            abort(400, 'Missing Stripe-Signature header.');
        }

        try {
            $event = app(\App\Services\StripeService::class)->constructWebhookEvent($payload, $signature);
        } catch (SignatureVerificationException $e) {
            Log::error('Assinatura do webhook Stripe inválida.', [
                'error' => $e->getMessage(),
            ]);
            abort(403, 'Invalid signature.');
        }

        // Registra o log do webhook (idempotência: stripe_event_id é UNIQUE)
        $webhookLog = $this->logWebhook($event);

        // Se já foi processado (duplicado), retorna 200 sem reprocessar
        if ($webhookLog->status === 'processed' || $webhookLog->status === 'skipped') {
            return response()->json(['status' => 'already_processed']);
        }

        // Despacha o job para processamento assíncrono via RabbitMQ (ou Redis)
        ProcessStripeWebhook::dispatch($event->id, $event->type);

        return response()->json(['status' => 'queued']);
    }

    /**
     * Registra ou atualiza o log do webhook no banco.
     *
     * Garante idempotência: se o evento já existe (UNIQUE constraint),
     * apenas retorna o registro existente sem criar duplicata.
     */
    private function logWebhook(\Stripe\Event $event): StripeWebhookLog
    {
        try {
            return StripeWebhookLog::create([
                'stripe_event_id' => $event->id,
                'event_type' => $event->type,
                'payload' => $event->toArray(),
                'status' => 'received',
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Evento duplicado — stripe_event_id já existe
            return StripeWebhookLog::where('stripe_event_id', $event->id)->firstOrFail();
        }
    }
}