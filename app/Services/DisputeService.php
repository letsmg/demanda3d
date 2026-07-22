<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DisputeService
{
    /**
     * Lista disputas visíveis para o usuário autenticado.
     *
     * - Sellers veem apenas disputas do seu tenant (GlobalScope)
     * - Admins podem ver disputas de todos os tenants (bypass)
     */
    public function listForUser(User $user, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = Dispute::with(['reporter', 'order', 'admin']);

        if ($user->isAdmin()) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Busca uma disputa específica com relacionamentos.
     */
    public function findForUser(int $disputeId, User $user): Dispute
    {
        $query = Dispute::with(['reporter', 'order', 'admin']);

        if ($user->isAdmin()) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
        }

        return $query->findOrFail($disputeId);
    }

    /**
     * Cria uma nova disputa vinculada a um pedido.
     *
     * Pipeline:
     *  1. Sanitiza a descrição via MessageSanitizer
     *  2. Criptografa e persiste
     */
    public function create(
        int $tenantId,
        int $reporterId,
        int $orderId,
        string $reason,
        string $description,
    ): Dispute {
        $result = MessageSanitizer::validate($description);

        if (! $result['valid']) {
            throw new \InvalidArgumentException($result['error']);
        }

        return DB::transaction(function () use ($tenantId, $reporterId, $orderId, $reason, $result) {
            return Dispute::create([
                'tenant_id'             => $tenantId,
                'reporter_id'           => $reporterId,
                'order_id'              => $orderId,
                'reason'                => $reason,
                'description_encrypted' => EncryptionService::encrypt($result['sanitized']),
                'status'                => 'pending',
            ]);
        });
    }

    /**
     * Atualiza o status da disputa (admin apenas).
     */
    public function updateStatus(Dispute $dispute, string $status, ?int $adminId = null): Dispute
    {
        $data = ['status' => $status];

        if ($adminId !== null) {
            $data['admin_id'] = $adminId;
        }

        $dispute->update($data);

        return $dispute;
    }

    /**
     * Fecha uma disputa como resolvida.
     */
    public function resolve(Dispute $dispute, int $adminId): Dispute
    {
        return $this->updateStatus($dispute, 'resolved', $adminId);
    }

    /**
     * Rejeita/descarta uma disputa.
     */
    public function dismiss(Dispute $dispute, int $adminId): Dispute
    {
        return $this->updateStatus($dispute, 'dismissed', $adminId);
    }

    /**
     * Envia uma mensagem no contexto da disputa.
     * Apenas admins podem enviar mensagens em disputas.
     */
    public function sendMessage(Dispute $dispute, string $content, int $senderId): void
    {
        $result = MessageSanitizer::validate($content);

        if (! $result['valid']) {
            throw new \InvalidArgumentException($result['error']);
        }

        // Disputas não usam a tabela messages padrão — usam thread vinculada à order
        // ou publicam diretamente na fila de disputas.
        dispatch(new \App\Jobs\PublishChatMessage(
            threadId: (string) $dispute->id,
            senderType: 'admin',
            senderId: (string) $senderId,
            content: $result['sanitized'],
            tenantId: (string) $dispute->tenant_id,
            channel: 'dispute',
        ))->onQueue('dispute_queue');
    }
}