<?php

namespace App\Scopes;

use App\Enums\UserAccessLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Automatically filters by tenant_id for Customer users.
     * Admin and Partner users bypass the scope (can see all data).
     */
    public function apply(Builder $builder, Model $model): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        if (! $user) {
            return;
        }

        // Admin and Partner can see all tenants data
        if ($user->access_level === UserAccessLevel::ADMIN || $user->access_level === UserAccessLevel::PARTNER) {
            return;
        }

        // Customer can only see their own tenant data
        $tenantId = $user->tenant?->id;

        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
    }
}
