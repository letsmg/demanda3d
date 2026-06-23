<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Ensure the client is authenticated or return 401.
     */
    private function guardClient(): ?\App\Models\Client
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            abort(response()->json(['error' => 'Unauthenticated'], 401));
        }
        return $client;
    }

    /**
     * Get all cart items for the authenticated client with product data.
     */
    public function index()
    {
        $client = $this->guardClient();
        $items = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes()->with('images');
        }])->where('client_id', $client->id)->get();

        return response()->json([
            'items' => $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'product' => $item->product,
                ];
            }),
            'total' => $items->sum(function ($item) {
                return (float) $item->product->price_sale * $item->quantity;
            }),
            'count' => $items->sum('quantity'),
        ]);
    }

    /**
     * Add or update a cart item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $client = $this->guardClient();

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

        return $this->index();
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $client = $this->guardClient();
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

        return $this->index();
    }

    /**
     * Remove a cart item.
     */
    public function destroy(CartItem $cartItem)
    {
        $client = $this->guardClient();
        if ($cartItem->client_id !== $client->id) {
            abort(403);
        }

        $cartItem->delete();

        return $this->index();
    }

    /**
     * Clear all cart items.
     */
    public function clear()
    {
        $client = $this->guardClient();
        CartItem::where('client_id', $client->id)->delete();

        return response()->json(['items' => [], 'total' => 0, 'count' => 0]);
    }
}