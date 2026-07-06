<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    /**
     * Listar produtos públicos (storefront), excluindo conteúdo adulto
     * para usuários não autorizados.
     *
     * Query params: search, min_price, max_price, sort, sort_dir, categoria
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'min_price', 'max_price', 'sort', 'sort_dir', 'category']);

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $canViewAdult = $user && $user->canAccessAdultContent();

        $products = $this->productService->listActiveForApi($filters, $canViewAdult);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'can_view_adult_content' => $canViewAdult,
            ],
        ]);
    }

    /**
     * Exibir detalhes de um produto pelo slug, incluindo cross-selling.
     *
     * O middleware CheckAgeRequirement já valida a idade antes de chegar aqui.
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $product = Product::withoutGlobalScopes()
            ->where('slug', $slug)
            ->with(['images', 'categories', 'tenant.user'])
            ->firstOrFail();

        // Validação explícita para produto adulto (defesa em profundidade)
        if ($product->hasAdultContent()) {
            /** @var \App\Models\User|null $user */
            $user = $request->user();

            if (!$user || !$user->canAccessAdultContent()) {
                return response()->json(['message' => 'Acesso restrito a maiores de 18 anos.'], 403);
            }
        }

        // Cross-selling: até 4 produtos relacionados que compartilham categorias
        $relatedProducts = $this->getRelatedProducts($product, $request);

        return response()->json([
            'data' => new ProductResource($product),
            'related_products' => ProductResource::collection($relatedProducts),
        ]);
    }

    /**
     * Obtém até 4 produtos relacionados que compartilham pelo menos uma categoria.
     * Exclui o produto atual e respeita restrição de conteúdo adulto.
     */
    private function getRelatedProducts(Product $product, Request $request)
    {
        $categoryIds = $product->categories()->pluck('id')->toArray();

        if (empty($categoryIds)) {
            return collect();
        }

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $canViewAdult = $user && $user->canAccessAdultContent();

        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('id', $categoryIds);
            })
            ->with(['images', 'categories'])
            ->limit(4);

        // Filtro de conteúdo adulto para usuários sem permissão
        if (!$canViewAdult) {
            $query->withoutAdultCategories();
        }

        return $query->get();
    }
}