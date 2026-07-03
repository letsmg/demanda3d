<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Jobs;

use App\Models\FreightContract;
use App\Models\Review;
use App\Services\EncryptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessarAvaliacaoTransportadora implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public int $freightContractId,
    ) {}

    /**
     * Auto-atribui nota após 10 dias sem avaliação do cliente/vendedor.
     *
     * O schema real de reviews suporta apenas: tenant_id, client_id,
     * order_id, rating, comment_encrypted.
     *
     * Usamos order_id = freightContractId (associação indireta) e
     * client_id = 0 (sistema). comment_encrypted é criptografado
     * com EncryptionService.
     */
    public function handle(): void
    {
        $contract = FreightContract::find($this->freightContractId);

        if (! $contract || ! $contract->delivered_at) {
            Log::warning('ProcessarAvaliacaoTransportadora: contrato não encontrado ou sem data de entrega.', [
                'freight_contract_id' => $this->freightContractId,
            ]);

            return;
        }

        // Verifica se já existe review para este contrato (via order_id)
        $exists = Review::withoutGlobalScopes()
            ->where('order_id', $contract->order_id ?? $contract->id)
            ->where('rating', 0) // rating 0 = auto-atribuída pelo sistema
            ->exists();

        if ($exists) {
            return;
        }

        $deadline = $contract->delivered_at->addDays(10);
        if (now()->lt($deadline)) {
            self::dispatch($this->freightContractId)->delay($deadline);

            return;
        }

        $commentData = EncryptionService::encryptWithHash(
            'Avaliação automática: prazo de entrega cumprido sem manifestação do vendedor.',
        );

        Review::create([
            'tenant_id'         => $contract->tenant_id,
            'client_id'         => 0,
            'order_id'          => $contract->order_id ?? $contract->id,
            'rating'            => 5,
            'comment_encrypted' => $commentData['encrypted'],
        ]);

        Log::info('ProcessarAvaliacaoTransportadora: avaliação automática registrada.', [
            'freight_contract_id' => $this->freightContractId,
            'delivered_at'        => $contract->delivered_at->toIso8601String(),
        ]);
    }
}
