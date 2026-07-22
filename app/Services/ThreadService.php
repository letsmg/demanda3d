<?php

namespace App\Services;

use App\Enums\UserAccessLevel;
use App\Models\Client;
use App\Models\Message;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ThreadService
{
    /**
     * Lista threads visíveis para o usuário autenticado.
     *
     * - Sellers veem apenas threads do seu tenant (GlobalScope)
     * - Admins podem ver threads de todos os tenants (bypass do scope)
     */
    public function listForUser(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Thread::with(['client', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }]);

        // Admins podem ver todos os tenants
        if ($user->isAdmin()) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        return $query->orderBy('updated_at', 'desc')->paginate($perPage);
    }

    /**
     * Busca uma thread específica com suas mensagens.
     */
    public function findForUser(int $threadId, User $user): Thread
    {
        $query = Thread::with(['client', 'order', 'messages' => function ($q) {
            $q->orderBy('created_at', 'asc');
        }]);

        if ($user->isAdmin()) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        return $query->findOrFail($threadId);
    }

    /**
     * Cria uma nova thread de dúvidas.
     */
    public function createThread(int $tenantId, int $clientId, ?int $orderId = null): Thread
    {
        return Thread::create([
            'tenant_id' => $tenantId,
            'client_id' => $clientId,
            'order_id'  => $orderId,
            'status'    => 'open',
        ]);
    }

    /**
     * Envia uma mensagem em uma thread.
     *
     * Pipeline:
     *  1. Sanitiza o conteúdo via MessageSanitizer (bloqueia se detectar PII)
     *  2. Criptografa e persiste
     *  3. Publica na fila RabbitMQ/Redis para processamento pelo Go Service
     */
    public function sendMessage(Thread $thread, string $content, string $senderType, int $senderId): Message
    {
        // Sanitização obrigatória contra PII
        $result = MessageSanitizer::validate($content);

        if (! $result['valid']) {
            throw new \InvalidArgumentException($result['error']);
        }

        $sanitized = $result['sanitized'];

        return DB::transaction(function () use ($thread, $sanitized, $senderType, $senderId) {
            $message = Message::create([
                'thread_id'          => $thread->id,
                'sender_type'        => $senderType,
                'sender_id'          => $senderId,
                'content_encrypted'  => \App\Services\EncryptionService::encrypt($sanitized),
            ]);

            // Atualiza timestamp da thread
            $thread->touch();

            // Publica na fila de chat para processamento assíncrono (Go Service)
            dispatch(new \App\Jobs\PublishChatMessage(
                threadId: (string) $thread->id,
                senderType: $senderType,
                senderId: (string) $senderId,
                content: $sanitized,
                tenantId: (string) $thread->tenant_id,
                channel: 'chat',
            ))->onQueue('chat_queue');

            return $message;
        });
    }

    /**
     * Fecha uma thread.
     */
    public function closeThread(Thread $thread): void
    {
        $thread->update(['status' => 'closed']);
    }

    /**
     * Reabre uma thread fechada.
     */
    public function reopenThread(Thread $thread): void
    {
        $thread->update(['status' => 'open']);
    }
}