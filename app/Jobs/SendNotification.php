<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

/**
 * Publica um payload de notificação na fila Redis (modo compatível)
 * ou diretamente no RabbitMQ para processamento pelo Go Service.
 *
 * Em ambiente de desenvolvimento, utiliza Redis como fallback.
 * Em produção com RabbitMQ habilitado, publica via AMQP.
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

        // Tenta RabbitMQ primeiro (se configurado), fallback para Redis
        if ($this->useRabbitMQ()) {
            $this->publishToRabbitMQ($payload);
        } else {
            $this->publishToRedis($payload);
        }
    }

    /**
     * Verifica se RabbitMQ está habilitado no ambiente.
     */
    private function useRabbitMQ(): bool
    {
        return config('queue.connections.rabbitmq.host') !== null
            && config('queue.connections.rabbitmq.host') !== '';
    }

    /**
     * Publica o payload na fila RabbitMQ 'notifications_queue'.
     */
    private function publishToRabbitMQ(string $payload): void
    {
        // Usa a conexão queue configurada para rabbitmq
        // O Laravel Queue Worker publica na exchange padrão com routing key = queue name
        // Neste caso, usamos o driver 'rabbitmq' configurado como conexão padrão ou
        // publicamos diretamente na queue 'notifications_queue'
        dispatch(new static(
            $this->userId,
            $this->title,
            $this->message,
            $this->channel,
            $this->tenantId,
        ))->onConnection('rabbitmq')->onQueue('notifications_queue');
    }

    /**
     * Publica o payload via RPUSH no Redis (fallback legado).
     */
    private function publishToRedis(string $payload): void
    {
        Redis::connection('default')
            ->rpush('notifications_queue', $payload);
    }
}