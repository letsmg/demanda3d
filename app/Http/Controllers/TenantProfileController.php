<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller para a página pública do perfil da loja (tenant).
 *
 * Exibe os dados públicos do tenant (logo, banner, nome fantasia)
 * e seus produtos ativos. Acessível por qualquer pessoa via /tenant/{fantasy_slug}.
 *
 * Se o tenant estiver bloqueado (active = false), retorna 404.
 */
class TenantProfileController extends Controller
{
    /**
     * GET /tenants — Lista pública de todos os tenants verificados.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'search'   => 'nullable|string|min:3|max:255',
            'sort'     => 'nullable|in:fantasy_name,rating_average,created_at',
            'sort_dir' => 'nullable|in:asc,desc',
        ]);

        $query = Tenant::with(['user:id,display_name,first_name_encrypted,last_name_encrypted', 'address'])
            ->whereNotNull('verified_at');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('fantasy_name', 'ilike', "%{$search}%")
                  ->orWhere('company_name', 'ilike', "%{$search}%");
            });
        }

        $sortField = $filters['sort'] ?? 'fantasy_name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowedSortFields = ['fantasy_name', 'rating_average', 'created_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('fantasy_name', 'asc');
        }

        $tenants = $query->paginate(12);

        return Inertia::render('Tenant/Index', [
            'tenants' => $tenants->items(),
            'filters' => $filters,
        ]);
    }

    /**
     * Exibe o perfil público da loja com seus produtos.
     */
    public function show(string $fantasySlug, Request $request): Response
    {
        $tenant = Tenant::where('fantasy_slug', $fantasySlug)
            ->where('active', true)
            ->firstOrFail();

        $filters = $request->validate([
            'search'    => 'nullable|string|min:3|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort'      => 'nullable|in:name,sale_price,created_at',
            'sort_dir'  => 'nullable|in:asc,desc',
            'category'  => 'nullable|string|exists:categories,slug',
        ]);

        // Verifica se o usuário pode ver conteúdo adulto
        $canViewAdult = false;
        $user = $request->user() ?? \Illuminate\Support\Facades\Auth::guard('clients')->user();
        if ($user && method_exists($user, 'canAccessAdultContent')) {
            $canViewAdult = $user->canAccessAdultContent();
        }

        // Produtos apenas deste tenant, ativos
        $productsQuery = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->where('tenant_id', $tenant->id)
            ->with(['images', 'categories']);

        if (! $canViewAdult) {
            $productsQuery->withoutAdultCategories();
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $productsQuery->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $productsQuery->where('sale_price', '>=', (float) $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $productsQuery->where('sale_price', '<=', (float) $filters['max_price']);
        }

        if (!empty($filters['category'])) {
            $productsQuery->whereHas('categories', function ($q) use ($filters) {
                $q->where('slug', $filters['category']);
            });
        }

        $sortField = $filters['sort'] ?? 'name';
        $sortDir = $filters['sort_dir'] ?? 'asc';
        $allowedSorts = ['name', 'sale_price', 'created_at'];
        if (in_array($sortField, $allowedSorts)) {
            $productsQuery->orderBy($sortField, $sortDir === 'desc' ? 'desc' : 'asc');
        }

        $products = $productsQuery->take(12)->get();
        $productResources = ProductResource::collection($products)->resolve();

        // Categorias apenas com produtos deste tenant
        $categories = Category::whereHas('products', function ($q) use ($tenant) {
            $q->where('tenant_id', $tenant->id)->where('is_active', true);
        })->orderBy('name')->get(['slug', 'name']);

        return Inertia::render('Tenant/Profile', [
            'tenant' => [
                'fantasy_name' => $tenant->fantasy_name,
                'company_name' => $tenant->company_name,
                'fantasy_slug' => $tenant->fantasy_slug,
                'logo_url'     => $tenant->logo_url,
                'banner_url'   => $tenant->banner_url,
                'state'        => $tenant->state,
                'city'         => $tenant->city,
                'rating_average' => $tenant->rating_average,
                'rating_count'   => $tenant->rating_count,
            ],
            'products'   => $productResources,
            'categories' => $categories,
            'filters'    => $filters,
        ]);
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.