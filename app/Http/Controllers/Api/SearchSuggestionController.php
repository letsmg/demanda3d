<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchSuggestionController extends Controller
{
    /**
     * Retorna até 6 sugestões de autocomplete baseadas no termo digitado.
     *
     * Fluxo de cache (Redis → Postgres → Redis):
     * A. Busca no Redis pela chave `autocomplete:{sha256(termo)}`
     * B. Cache miss → consulta PostgreSQL (names + categories)
     * C. Salva resultado no Redis (TTL: 30 minutos)
     */
    public function __invoke(Request $request): JsonResponse
    {
        $term = trim($request->get('q', ''));

        if (mb_strlen($term) < 1) {
            return response()->json(['suggestions' => []]);
        }

        $normalized = mb_strtolower($term);
        $cacheKey = 'autocomplete:' . hash('sha256', $normalized);

        // ── A. Redis lookup ──────────────────────────────────
        $cached = Cache::get($cacheKey);

        if ($cached !== null) {
            return response()->json(['suggestions' => $cached]);
        }

        // ── B. PostgreSQL fallback ────────────────────────────
        $suggestions = $this->queryPostgres($normalized);

        // ── C. Store in Redis ─────────────────────────────────
        Cache::put($cacheKey, $suggestions, now()->addMinutes(30));

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Busca sugestões no PostgreSQL: nomes de produtos + nomes de categorias.
     * Filtra apenas produtos ativos de tenants ativos e completos.
     */
    private function queryPostgres(string $term): array
    {
        $likeTerm = DB::connection()->getPdo()->quote('%' . $term . '%');

        // Busca em produtos ativos (nomes distintos, até 6)
        $productNames = DB::select("
            SELECT DISTINCT p.name AS suggestion, 'product' AS type
            FROM products p
            INNER JOIN tenants t ON t.id = p.tenant_id
                AND t.active = true
                AND t.is_profile_complete = true
            WHERE p.is_active = true
              AND p.deleted_at IS NULL
              AND LOWER(p.name) LIKE LOWER({$likeTerm})
            LIMIT 6
        ");

        // Busca em categorias (nomes distintos, até 3)
        $categoryNames = DB::select("
            SELECT DISTINCT c.name AS suggestion, 'category' AS type
            FROM categories c
            INNER JOIN category_product cp ON cp.category_id = c.id
            INNER JOIN products p ON p.id = cp.product_id
                AND p.is_active = true
                AND p.deleted_at IS NULL
            INNER JOIN tenants t ON t.id = p.tenant_id
                AND t.active = true
                AND t.is_profile_complete = true
            WHERE LOWER(c.name) LIKE LOWER({$likeTerm})
            LIMIT 3
        ");

        // Merge: prioriza produtos, depois categorias
        $all = array_merge(
            array_map(fn ($r) => $r->suggestion, $productNames),
            array_map(fn ($r) => $r->suggestion, $categoryNames),
        );

        // Remove duplicatas e limita a 6
        $unique = array_values(array_unique($all));

        return array_slice($unique, 0, 6);
    }
}