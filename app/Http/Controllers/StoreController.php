<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
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
        $filters = $request->validate([
            'search' => 'nullable|string|min:3|max:255',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'sort' => 'nullable|in:name,sale_price,created_at',
            'sort_dir' => 'nullable|in:asc,desc',
        ]);

        $products = $this->productService->listActiveForStore($filters);

        return Inertia::render('Store/Index', [
            'products' => $products,
            'filters' => $filters,
        ]);
    }
}