<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardSearchService
{
    public function search(string $model, string $term, string $tenantId): array
    {
        $term = trim(strip_tags($term));

        if (strlen($term) < 3 || empty($tenantId) || $tenantId === '0') {
            return [];
        }

        $cacheKey = "dashboard:search:{$model}:" . hash('sha256', strtolower($term) . $tenantId);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $results = match ($model) {
            'products' => $this->searchProducts($term, $tenantId),
            'clients' => $this->searchClients($term, $tenantId),
            'orders' => $this->searchOrders($term, $tenantId),
            'inputs' => $this->searchInputs($term, $tenantId),
            default => [],
        };

        if (!empty($results)) {
            Cache::put($cacheKey, $results, now()->addMinutes(10));
        }

        return $results;
    }

    private function searchProducts(string $term, string $tenantId): array
    {
        return DB::connection('pgsql')->table('products')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($term) {
                $q->where('name', 'ilike', "%{$term}%")
                  ->orWhere('description', 'ilike', "%{$term}%");
            })
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->toArray();
    }

    private function searchClients(string $term, string $tenantId): array
    {
        return DB::connection('pgsql')->table('clients')
            ->where('tenant_id', $tenantId)
            ->where('display_name', 'ilike', "%{$term}%")
            ->orderBy('display_name')
            ->limit(50)
            ->get()
            ->toArray();
    }

    private function searchOrders(string $term, string $tenantId): array
    {
        return DB::connection('pgsql')->table('orders')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->where('orders.tenant_id', $tenantId)
            ->where(function ($q) use ($term) {
                $q->where('clients.display_name', 'ilike', "%{$term}%")
                  ->orWhere('orders.status', 'ilike', "%{$term}%");
            })
            ->select('orders.*', 'clients.display_name as client_display_name')
            ->orderBy('orders.created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }

    private function searchInputs(string $term, string $tenantId): array
    {
        return DB::connection('pgsql')->table('inputs')
            ->where('tenant_id', $tenantId)
            ->where(function ($q) use ($term) {
                $q->where('description', 'ilike', "%{$term}%")
                   ->orWhere('brand', 'ilike', "%{$term}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
}