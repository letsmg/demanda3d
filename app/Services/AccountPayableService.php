<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\AccountPayable;

class AccountPayableService
{
    public function create(array $data): AccountPayable
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        return AccountPayable::create($data);
    }

    public function update(AccountPayable $account, array $data): AccountPayable
    {
        $account->update($data);

        return $account;
    }

    public function delete(AccountPayable $account): bool
    {
        return $account->delete();
    }
}