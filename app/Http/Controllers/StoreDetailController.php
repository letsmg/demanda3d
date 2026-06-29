<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller para a página pública de detalhes do produto na store.
 *
 * Renderiza a view Vue.js com os dados completos do produto + cross-selling
 * via Inertia, acessível pela rota /store/{slug}.
 */
class StoreDetailController extends Controller
{
    /**
     * Exibe a página de detalhes do produto pelo slug.
     */
    public function show(string $slug, Request $request): Response
    {
        $product = Product::withoutGlobalScopes()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['images', 'categorias', 'tenant.user'])
            ->firstOrFail();

        // Defesa em profundidade: validação explícita para produto adulto
        if ($product->hasAdultContent()) {
            /** @var \App\Models\User|null $user */
            $user = $request->user();

            if (!$user || !$user->canAccessAdultContent()) {
                abort(403, 'Acesso restrito a maiores de 18 anos.');
            }
        }

        // Cross-selling: até 4 produtos da mesma categoria
        $relatedProducts = $this->getRelatedProducts($product, $request);

        $productResource = (new ProductResource($product))->resolve();

        $relatedResources = ProductResource::collection($relatedProducts)->resolve();

        return Inertia::render('Store/Detail', [
            'product' => $productResource,
            'relatedProducts' => $relatedResources,
        ]);
    }

    /**
     * Obtém até 4 produtos relacionados que compartilham pelo menos uma categoria.
     */
    private function getRelatedProducts(Product $product, Request $request)
    {
        $categoriaIds = $product->categorias()->pluck('categoria_id')->toArray();

        if (empty($categoriaIds)) {
            return collect();
        }

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $canViewAdult = $user && $user->canAccessAdultContent();

        $query = Product::withoutGlobalScopes()
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->whereHas('categorias', function ($q) use ($categoriaIds) {
                $q->whereIn('categoria_id', $categoriaIds);
            })
            ->with(['images', 'categorias'])
            ->limit(4);

        if (!$canViewAdult) {
            $query->withoutAdultCategories();
        }

        return $query->get();
    }
}