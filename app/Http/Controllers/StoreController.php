<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(private ProductService $productService) {}

    /**
     * Display the public store (vitrine) with all active products.
     * No tenant filtering — all producers' products are shown.
     */
    public function index(Request $request): Response
    {
        \Log::info('DADOS DO REQUEST:', $request->all());
        $filters = $request->validate([            
            'search'      => 'nullable|string|min:3|max:255',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'sort'        => 'nullable|in:name,sale_price,created_at',
            'sort_dir'    => 'nullable|in:asc,desc',
            'categories'  => 'nullable|string|max:500', // comma-separated category slugs
        ]);
        \Log::info('FILTROS ENVIADOS AO SERVICE:', $filters); // <--- ADICIONE ISSO
        // Verifica se o usuário pode ver conteúdo adulto (18+)
        $canViewAdult = false;
        $user = $request->user() ?? \Illuminate\Support\Facades\Auth::guard('clients')->user();
        if ($user && method_exists($user, 'canAccessAdultContent')) {
            $canViewAdult = $user->canAccessAdultContent();
        }
        
        $products = $this->productService->listActiveForStore($filters, $canViewAdult);
        \Log::info('Produtos encontrados:', ['count' => count($products)]);
        \Log::info('QTD PRODUTOS RETORNADOS:', ['count' => count($products)]); // <--- ADICIONE ISSO
        // Filtra categorias visíveis: sem "adulto" para menores
        $categoriesQuery = Category::orderBy('name');
        if (! $canViewAdult) {
            $categoriesQuery->where('is_adult', false);
        }
        $categories = $categoriesQuery->get(['slug', 'name']);

        return Inertia::render('Store/Index', [
            'products'   => $products->toArray(),
            'categories' => $categories->toArray(),
            'filters'    => $filters,
        ]);
    }

    /**
     * API endpoint for lazy-loading more products ("mostrar mais").
     *
     * Returns a JSON response with paginated products + has_more flag.
     */
    public function moreProducts(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'search'      => 'nullable|string|max:255',
            'min_price'   => 'nullable|numeric|min:0',
            'max_price'   => 'nullable|numeric|min:0',
            'sort'        => 'nullable|in:name,sale_price,created_at',
            'sort_dir'    => 'nullable|in:asc,desc',
            'categories'  => 'nullable|string|max:500', // comma-separated category slugs
            'page'        => 'required|integer|min:1',
        ]);

        $page = (int) ($filters['page'] ?? 1);
        unset($filters['page']);

        $canViewAdult = false;
        $user = $request->user() ?? \Illuminate\Support\Facades\Auth::guard('clients')->user();
        if ($user && method_exists($user, 'canAccessAdultContent')) {
            $canViewAdult = $user->canAccessAdultContent();
        }

        $result = $this->productService->paginateActiveForStore($filters, $canViewAdult, $page, 24);

        return response()->json([
            'data'     => $result['data'],
            'has_more' => $result['has_more'],
        ]);
    }
}
