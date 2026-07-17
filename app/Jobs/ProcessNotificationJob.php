<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job de exemplo para validar a integração com RabbitMQ.
 *
 * Este Job simula o processamento assíncrono de uma notificação
 * enviada através da fila configurada (RabbitMQ ou Redis).
 *
 * Uso: ProcessNotificationJob::dispatch($userId, $title, $message);
 */
class ProcessNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $userId,
        public string $title,
        public string $message,
    ) {}

    /**
     * Execute the job.
     *
     * Simula o processamento de notificação e registra no log.
     * Em produção, este Job seria consumido pela fila RabbitMQ
     * e processaria o disparo real (push, e-mail, SMS).
     */
    public function handle(): void
    {
        Log::info('[ProcessNotificationJob] Notificação processada via queue.', [
            'queue_driver' => config('queue.default'),
            'user_id'      => $this->userId,
            'title'        => $this->title,
            'message'      => $this->message,
            'timestamp'    => now()->toIso8601String(),
        ]);
    }
}