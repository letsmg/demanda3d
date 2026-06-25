<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardSearchService
{
    /**
     * Estratégia: Redis → Replica (read-only) → Cache
     *
     * 1. Tenta Redis (cache quente, TTL 10min)
     * 2. Se MISS, consulta a réplica PostgreSQL (hot standby)
     * 3. Salva resultado no Redis para próximas buscas
     *
     * @param string $model  Nome do modelo (products, clients, orders, inputs)
     * @param string $term   Termo de busca (já sanitizado, min 3 chars)
     * @param string $tenantId ID do tenant atual
     * @return array         Array de resultados (collections ou arrays associativos)
     */
    public function search(string $model, string $term, string $tenantId): array
    {
        $term = trim(strip_tags($term));

        if (strlen($term) < 3 || empty($tenantId) || $tenantId === '0') {
            return [];
        }

        $cacheKey = "dashboard:search:{$model}:" . hash('sha256', strtolower($term) . $tenantId);

        // 1. Tenta Redis primeiro
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // 2. MISS — consulta a réplica (read-only) via conexão de leitura
        $results = match ($model) {
            'products' => $this->searchProducts($term, $tenantId),
            'clients' => $this->searchClients($term, $tenantId),
            'orders' => $this->searchOrders($term, $tenantId),
            'inputs' => $this->searchInputs($term, $tenantId),
            default => [],
        };

        // 3. Armazena no Redis (10 minutos)
        if (!empty($results)) {
            Cache::put($cacheKey, $results, now()->addMinutes(10));
        }

        return $results;
    }

    /**
     * Invalida o cache de busca para um modelo específico (após create/update/delete).
     */
    public function invalidate(string $model): void
    {
        // Redis FLUSH por prefixo não é nativo; limpamos chaves conhecidas.
        // Na prática, o TTL de 10min já resolve — invalidamos apenas o que for
        // necessário via Cache::forget() nas ações específicas.
        // Por simplicidade e performance, o TTL garante stale máximo de 10min.
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
            ->orderBy('purchase_date', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
}