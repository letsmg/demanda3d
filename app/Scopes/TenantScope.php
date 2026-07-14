<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Automatically filters by tenant_id for Customer users.
     * Staff (Admin, Partner, Operational) can see their own tenant data.
     * Unauthenticated users bypass the scope.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Se o usuário autenticado é um Client (guard clients),
        // não aplicar TenantScope — cliente acessa apenas seus próprios dados
        if ($user instanceof \App\Models\Client) {
            return;
        }

        // Staff (sellers + admin): filter by their own tenant
        if ($user instanceof \App\Models\User && $user->isStaff()) {
            $tenantId = $user->tenant?->id;
            if ($tenantId) {
                $builder->where('tenant_id', $tenantId);
            }
        }
    }
}