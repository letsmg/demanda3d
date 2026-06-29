<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller para a página pública de detalhes do produto.
 *
 * Renderiza a view Vue.js com os dados completos do produto via Inertia,
 * incluindo SEO meta tags e cross-selling via API.
 */
class ProductDetailController extends Controller
{
    /**
     * Exibe a página de detalhes do produto pelo slug.
     *
     * O middleware CheckAgeRequirement valida a idade antes de chegar aqui.
     */
    public function show(string $slug, Request $request): Response
    {
        $product = Product::withoutGlobalScopes()
            ->where('slug', $slug)
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

        $productResource = new ProductResource($product);

        return Inertia::render('Product/Detail', [
            'product' => $productResource->resolve(),
        ]);
    }
}