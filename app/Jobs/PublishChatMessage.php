<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

/**
 * Publica uma mensagem de chat (dúvidas ou disputas) na fila
 * para processamento assíncrono pelo Go Notification & Chat Service.
 *
 * O Go Service escuta as filas 'chat_queue' e 'dispute_queue' via RabbitMQ
 * e executa o pipeline de triagem (FAQ chatbot) e logging estruturado.
 */
class PublishChatMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $threadId,
        public string $senderType,
        public string $senderId,
        public string $content,
        public string $tenantId,
        public string $channel = 'chat', // 'chat' para dúvidas, 'dispute' para disputas
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payload = json_encode([
            'thread_id'   => $this->threadId,
            'sender_type' => $this->senderType,
            'sender_id'   => $this->senderId,
            'content'     => $this->content, // já sanitizado pelo MessageSanitizer
            'tenant_id'   => $this->tenantId,
            'channel'     => $this->channel,
            'timestamp'   => now()->toIso8601String(),
        ], JSON_THROW_ON_ERROR);

        $queueName = $this->channel === 'dispute' ? 'dispute_queue' : 'chat_queue';

        // Fallback para Redis (RabbitMQ é gerenciado pelo Go Service diretamente)
        // O Go Service consome do RabbitMQ; o Laravel publica no Redis como buffer
        Redis::connection('default')->rpush($queueName, $payload);
    }
}