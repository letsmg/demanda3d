<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    public function __construct(private ProductService $productService) {}

    /**
     * Display the public store (vitrine) with all active products.
     * No tenant filtering — all producers' products are shown.
     */
    public function index(): Response
    {
        $products = $this->productService->listActiveForStore();

        return Inertia::render('Store/Index', [
            'products' => $products,
        ]);
    }
}