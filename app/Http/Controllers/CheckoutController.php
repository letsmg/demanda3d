<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    /**
     * Create a Stripe Checkout Session and redirect the client.
     */
    public function store(Request $request)
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            return redirect('/login_cli');
        }

        $cartItems = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes()->with('images');
        }])->where('client_id', $client->id)->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Your cart is empty.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $cartItems->map(function ($item) {
            $unitAmount = (int) round((float) $item->product->price_sale * 100);
            return [
                'price_data' => [
                    'currency' => 'brl',
                    'product_data' => [
                        'name' => $item->product->name,
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => route('checkout.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel', [], true),
        ]);

        // Persist order record
        Order::create([
            'client_id' => $client->id,
            'stripe_session_id' => $session->id,
            'amount_total' => $session->amount_total / 100,
            'currency' => $session->currency,
            'status' => 'pending',
        ]);

        // Redirect to Stripe
        return Inertia::location($session->url);
    }

    /**
     * Show success page after Stripe redirect.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $order = Order::where('stripe_session_id', $sessionId)->first();
                if ($order && $order->status === 'pending') {
                    $order->update(['status' => 'paid']);

                    // Clear the cart
                    $client = Auth::guard('clients')->user();
                    if ($client) {
                        CartItem::where('client_id', $client->id)->delete();
                    }
                }
            }
        }

        return Inertia::render('Client/CheckoutSuccess', [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Show cancel page.
     */
    public function cancel()
    {
        return Inertia::render('Client/CheckoutCancel');
    }
}