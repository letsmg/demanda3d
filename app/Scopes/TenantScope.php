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
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Staff (sellers + admin): filter by their own tenant
        if ($user->isStaff()) {
            $tenantId = $user->tenant?->id;
            if ($tenantId) {
                $builder->where('tenant_id', $tenantId);
            }
            return;
        }

        // Customer: filter by their own tenant
        $tenantId = $user->tenant?->id;
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
    }
}