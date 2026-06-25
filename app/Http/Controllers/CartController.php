<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Build the cart data array from the given client.
     */
    private function buildCartData(\App\Models\Client $client): array
    {
        $items = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes()->with('images');
        }])->where('client_id', $client->id)->get();

        return [
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'sale_price' => $item->product->sale_price,
                        'image_url' => $item->product->images->first()?->url ?? null,
                    ],
                ];
            })->values(),
            'total' => $items->sum(function ($item) {
                return (float) $item->product->sale_price * $item->quantity;
            }),
            'count' => $items->sum('quantity'),
        ];
    }

    /**
     * Ensure the client is authenticated or respond appropriately.
     */
    private function guardClient(bool $json = false): \App\Models\Client
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            if ($json) {
                abort(response()->json(['error' => 'Unauthenticated'], 401));
            }
            abort(redirect('/login_cli'));
        }
        return $client;
    }

    /**
     * GET /cart/items — JSON API for fetch calls.
     */
    public function index()
    {
        $client = $this->guardClient(true);
        return response()->json($this->buildCartData($client));
    }

    /**
     * GET /cart — Inertia page.
     */
    public function show()
    {
        $client = $this->guardClient(false);
        return \Inertia\Inertia::render('Client/Cart', $this->buildCartData($client));
    }

    /**
     * POST /cart — add item
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $client = $this->guardClient(true);

        $item = CartItem::where('client_id', $client->id)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $validated['quantity']]);
        } else {
            CartItem::create([
                'client_id' => $client->id,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return response()->json($this->buildCartData($client));
    }

    /**
     * PUT /cart/{cartItem} — update quantity
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $client = $this->guardClient(true);
        if ($cartItem->client_id !== $client->id) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        if ($validated['quantity'] === 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $validated['quantity']]);
        }

        return response()->json($this->buildCartData($client));
    }

    /**
     * DELETE /cart/{cartItem} — remove item
     */
    public function destroy(CartItem $cartItem)
    {
        $client = $this->guardClient(true);
        if ($cartItem->client_id !== $client->id) {
            abort(403);
        }

        $cartItem->delete();

        return response()->json($this->buildCartData($client));
    }

    /**
     * POST /cart/clear — clear all items
     */
    public function clear()
    {
        $client = $this->guardClient(true);
        CartItem::where('client_id', $client->id)->delete();

        return response()->json($this->buildCartData($client));
    }
}