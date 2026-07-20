<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Builder;

/**
 * Serviço de busca híbrida — Redis (cache) + PostgreSQL (fallback) + Meilisearch (full-text).
 *
 * Estratégia em 3 camadas:
 * 1. Redis: cache de queries repetidas (TTL 10 min)
 * 2. PostgreSQL: fallback nativo quando Meilisearch está indisponível
 * 3. Meilisearch: busca full-text com fuzzy search para o catálogo público
 */
class MeilisearchService
{
    /**
     * Busca produtos no catálogo público usando a estratégia híbrida.
     *
     * @param  string      $query     Termo de busca textual
     * @param  array       $filters   Filtros adicionais (min_price, max_price, categories, etc.)
     * @param  int         $perPage   Itens por página
     * @param  int         $page      Página atual
     * @return array{data: \Illuminate\Pagination\LengthAwarePaginator, engine: string}
     */
    public function searchPublicCatalog(string $query, array $filters = [], int $perPage = 20, int $page = 1): array
    {
        $cacheKey = $this->buildCacheKey('search:v2', $query, $filters, $perPage, $page);

        // ── Camada 1: Redis Cache ──────────────────────────────────
        if ($cacheKey !== null) {
            $cached = Cache::get($cacheKey);

            if ($cached !== null && is_array($cached)) {
                return [
                    ...$cached,
                    'engine' => 'redis-cache',
                ];
            }
        }

        // ── Camada 2: Meilisearch (preferencial) ───────────────────
        if ($this->isMeilisearchEnabled()) {
            try {
                $result = $this->searchViaMeilisearch($query, $filters, $perPage, $page);

                if ($cacheKey !== null) {
                    Cache::put($cacheKey, $result, now()->addMinutes(10));
                }

                return [
                    ...$result,
                    'engine' => 'meilisearch',
                ];
            } catch (\Throwable $e) {
                Log::warning('Meilisearch indisponível, usando fallback PostgreSQL.', [
                    'error' => $e->getMessage(),
                    'query' => $query,
                ]);
            }
        }

        // ── Camada 3: PostgreSQL (fallback) ────────────────────────
        $result = $this->searchViaPostgres($query, $filters, $perPage, $page);

        if ($cacheKey !== null) {
            Cache::put($cacheKey, $result, now()->addMinutes(5));
        }

        return [
            ...$result,
            'engine' => 'postgres',
        ];
    }

    /**
     * Verifica se o Meilisearch está habilitado e disponível.
     */
    public function isMeilisearchEnabled(): bool
    {
        return config('scout.driver') === 'meilisearch'
            && ! empty(config('scout.meilisearch.host'))
            && ! empty(config('scout.meilisearch.key'));
    }

    /**
     * Executa busca textual via Meilisearch com filtros.
     */
    private function searchViaMeilisearch(string $query, array $filters, int $perPage, int $page): array
    {
        /** @var \Laravel\Scout\Builder $scoutBuilder */
        $scoutBuilder = Product::search($query);

        // Aplica filtros do Meilisearch
        $filterExpressions = [];

        // Apenas produtos ativos
        $filterExpressions[] = 'is_active = true';

        if (! empty($filters['min_price'])) {
            $filterExpressions[] = 'sale_price >= ' . (float) $filters['min_price'];
        }

        if (! empty($filters['max_price'])) {
            $filterExpressions[] = 'sale_price <= ' . (float) $filters['max_price'];
        }

        if (! empty($filters['tenant_id'])) {
            $filterExpressions[] = 'tenant_id = ' . (int) $filters['tenant_id'];
        }

        if (! empty($filterExpressions)) {
            $scoutBuilder->where(implode(' AND ', $filterExpressions));
        }

        // Ordenação
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $scoutBuilder->orderBy($sortField, $sortDir);

        // Paginação
        $results = $scoutBuilder->paginate($perPage, 'page', $page);

        // Load relationships para exibição
        $results->load(['images', 'tenant.user', 'categories']);

        // Filtro de conteúdo adulto (pós-busca, pois Meilisearch não tem o scope)
        if (empty($filters['can_view_adult'])) {
            $results->setCollection(
                $results->getCollection()->filter(function (Product $product) {
                    return ! $product->hasAdultContent();
                })->values()
            );
        }

        // Filtro adicional de categorias (pós-busca)
        if (! empty($filters['categories'])) {
            $categorySlugs = is_array($filters['categories'])
                ? $filters['categories']
                : array_filter(array_map('trim', explode(',', $filters['categories'])));

            if (! empty($categorySlugs)) {
                $results->setCollection(
                    $results->getCollection()->filter(function (Product $product) use ($categorySlugs) {
                        return $product->categories->pluck('slug')->intersect($categorySlugs)->isNotEmpty();
                    })->values()
                );
            }
        }

        return [
            'data' => $results,
            'total' => $results->total(),
            'has_more' => $results->hasMorePages(),
        ];
    }

    /**
     * Busca via PostgreSQL nativo (fallback quando Meilisearch está offline).
     */
    private function searchViaPostgres(string $query, array $filters, int $perPage, int $page): array
    {
        $queryBuilder = Product::withoutGlobalScopes()
            ->availableForSale()
            ->whereHas('tenant', function ($q) {
                $q->where('active', true)
                  ->where('is_profile_complete', true);
            })
            ->with(['images', 'tenant.user', 'categories']);

        // Filtro de conteúdo adulto
        if (empty($filters['can_view_adult'])) {
            $queryBuilder->withoutAdultCategories();
        }

        // Busca textual
        if (! empty($query) && strlen($query) >= 3) {
            $queryBuilder->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                  ->orWhere('description', 'ilike', "%{$query}%")
                  ->orWhere('material_type', 'ilike', "%{$query}%");
            });
        }

        // Filtros de preço
        if (! empty($filters['min_price'])) {
            $queryBuilder->where('sale_price', '>=', (float) $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $queryBuilder->where('sale_price', '<=', (float) $filters['max_price']);
        }

        // Filtro de categorias
        if (! empty($filters['categories'])) {
            $categorySlugs = is_array($filters['categories'])
                ? $filters['categories']
                : array_filter(array_map('trim', explode(',', $filters['categories'])));

            if (! empty($categorySlugs)) {
                $queryBuilder->whereHas('categories', function ($q) use ($categorySlugs) {
                    $q->whereIn('slug', $categorySlugs);
                });
            }
        }

        // Ordenação
        $sortField = in_array($filters['sort'] ?? 'name', ['name', 'sale_price', 'created_at'])
            ? $filters['sort']
            : 'name';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $queryBuilder->orderBy($sortField, $sortDir);

        $results = $queryBuilder->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $results,
            'total' => $results->total(),
            'has_more' => $results->hasMorePages(),
        ];
    }

    /**
     * Sincroniza um produto individualmente no Meilisearch.
     *
     * Útil após create/update para manter o índice atualizado em tempo real.
     */
    public function syncProduct(Product $product): void
    {
        if (! $this->isMeilisearchEnabled()) {
            return;
        }

        try {
            if ($product->shouldBeSearchable()) {
                $product->searchable();
            } else {
                $product->unsearchable();
            }
        } catch (\Throwable $e) {
            Log::warning('Falha ao sincronizar produto no Meilisearch.', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove um produto do índice Meilisearch.
     */
    public function removeProduct(Product $product): void
    {
        if (! $this->isMeilisearchEnabled()) {
            return;
        }

        try {
            $product->unsearchable();
        } catch (\Throwable $e) {
            Log::warning('Falha ao remover produto do Meilisearch.', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reindexa todos os produtos elegíveis no Meilisearch.
     *
     * Comando: php artisan scout:import "App\Models\Product"
     */
    public function reindexAll(): int
    {
        if (! $this->isMeilisearchEnabled()) {
            Log::info('Meilisearch desabilitado. Reindexação ignorada.');

            return 0;
        }

        $count = 0;

        Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->whereHas('tenant', function ($q) {
                $q->where('active', true)
                  ->whereHas('user', function ($uq) {
                      $uq->whereNotNull('email_verified_at');
                  });
            })
            ->chunk(500, function ($products) use (&$count) {
                Product::searchable($products);
                $count += $products->count();
            });

        Log::info('Reindexação Meilisearch concluída.', ['count' => $count]);

        return $count;
    }

    /**
     * Limpa o índice de produtos do Meilisearch.
     */
    public function flushIndex(): void
    {
        if (! $this->isMeilisearchEnabled()) {
            return;
        }

        try {
            Product::removeAllFromSearch();
            Log::info('Índice Meilisearch de produtos limpo.');
        } catch (\Throwable $e) {
            Log::warning('Falha ao limpar índice Meilisearch.', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Constrói uma chave de cache determinística baseada nos parâmetros da busca.
     */
    private function buildCacheKey(string $prefix, string $query, array $filters, int $perPage, int $page): ?string
    {
        // Não cacheia queries muito simples (sem termo de busca relevante)
        $hasRelevantFilters = strlen($query ?? '') >= 3
            || ! empty($filters['min_price'])
            || ! empty($filters['max_price'])
            || ! empty($filters['categories']);

        if (! $hasRelevantFilters) {
            return null;
        }

        $payload = implode('|', [
            $prefix,
            strtolower(trim($query ?? '')),
            $filters['min_price'] ?? '0',
            $filters['max_price'] ?? '0',
            $filters['categories'] ?? '',
            $filters['sort'] ?? 'name',
            $filters['sort_dir'] ?? 'asc',
            $perPage,
            $page,
            $filters['can_view_adult'] ?? '0',
        ]);

        return 'meili:' . hash('sha256', $payload);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.