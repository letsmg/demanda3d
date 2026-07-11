<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

/**
 * Publica um payload de notificação na fila Redis
 * que o microsserviço Go (go-notification-service) consome.
 *
 * Este Job NÃO processa a notificação localmente —
 * apenas serializa e publica na fila notifications_queue.
 * O worker Go é responsável por disparar push/FCM, e-mail e SMS.
 */
class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $userId,
        public string $title,
        public string $message,
        public string $channel = 'push',
        public ?string $tenantId = null,
    ) {}

    /**
     * Execute the job.
     *
     * Publica o payload como JSON na fila Redis 'notifications_queue'.
     * O Go Notification Service escuta esta fila via BLPOP e processa
     * o disparo real de forma assíncrona e concorrente.
     */
    public function handle(): void
    {
        $payload = json_encode([
            'user_id'   => $this->userId,
            'title'     => $this->title,
            'message'   => $this->message,
            'channel'   => $this->channel,
            'tenant_id' => $this->tenantId,
            'timestamp' => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        Redis::connection('default')
            ->rpush('notifications_queue', $payload);
    }
}