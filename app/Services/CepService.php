<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\State;

class CepService
{
    /**
     * Retorna os dados do estado correspondente ao CEP informado.
     *
     * @return array{state_id: int|null, uf: string|null, state_name: string|null}
     */
    public function lookup(string $cep): array
    {
        $state = State::findByCep($cep);

        if (! $state) {
            return [
                'state_id'   => null,
                'uf'         => null,
                'state_name' => null,
            ];
        }

        return [
            'state_id'   => $state->id,
            'uf'         => $state->uf,
            'state_name' => $state->name,
        ];
    }
}