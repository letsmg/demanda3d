<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\FreightContract;

class FreightContractService
{
    public function create(array $data): FreightContract
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        return FreightContract::create($data);
    }

    public function update(FreightContract $contract, array $data): FreightContract
    {
        $contract->update($data);

        return $contract;
    }

    public function delete(FreightContract $contract): bool
    {
        return $contract->delete();
    }
}