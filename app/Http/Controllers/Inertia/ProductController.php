<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\DashboardSearchService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService,
        private DashboardSearchService $searchService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->get('search');

        $products = ($search && strlen($search) >= 3 && auth()->user()->tenant_id)
            ? $this->searchService->search('products', $search, (string) auth()->user()->tenant_id)
            : Product::orderBy('created_at', 'desc')->paginate($request->get('per_page', 10))->withQueryString();

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create');
    }

    public function store(StoreProductRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->productService->update($product, $request->validated());

        return redirect()->route('products.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);

        return redirect()->route('products.index')
            ->with('success', 'Produto excluído com sucesso.');
    }
}
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.
