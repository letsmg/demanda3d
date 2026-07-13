<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function __construct(
        private CheckoutService $checkoutService,
    ) {}

    /**
     * Exibe a tela multi-etapas de checkout.
     */
    public function show(Request $request)
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            return redirect('/login_cli');
        }

        $cartItems = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes();
        }])->where('client_id', $client->id)->get();

        if ($cartItems->isEmpty()) {
            return redirect('/cart')->with('error', 'Seu carrinho está vazio.');
        }

        $total = $cartItems->sum(fn ($item) => (float) $item->product->sale_price * $item->quantity);

        // Busca transportadoras vinculadas aos vendedores dos produtos no carrinho
        $productTenantIds = $cartItems->pluck('product.tenant_id')->unique()->filter();
        $carriers = \App\Models\Carrier::whereIn('id', function ($query) use ($productTenantIds) {
            $query->select('carrier_id')
                ->from('vendor_carrier')
                ->whereIn('user_id', function ($sub) use ($productTenantIds) {
                    $sub->select('id')->from('users')
                        ->whereIn('id', \App\Models\Tenant::whereIn('id', $productTenantIds)->pluck('user_id'));
                })
                ->where('status', 'approved');
        })->where('is_active', true)->where('is_blocked', false)->get(['id', 'name']);

        return Inertia::render('Client/Checkout', [
            'client'    => $client,
            'addresses' => [$client->only(['id', 'address', 'number', 'city', 'state', 'zipcode'])],
            'carriers'  => $carriers,
            'total'     => $total,
            'count'     => $cartItems->sum('quantity'),
        ]);
    }

    /**
     * Cria uma Stripe Checkout Session, persiste o pedido via CheckoutService
     * e redireciona o cliente para o Stripe.
     */
    public function store(Request $request)
    {
        $client = Auth::guard('clients')->user();
        if (! $client) {
            return redirect('/login_cli');
        }

        $validated = $request->validate([
            'address_id'     => ['nullable', 'integer'],
            'coupon_code'    => ['nullable', 'string', 'max:50'],
            'carrier_id'     => ['nullable', 'integer', 'exists:carriers,id'],
            'payment_method' => ['required', 'in:card,boleto,pix'],
        ]);

        $cartItems = CartItem::with(['product' => function ($q) {
            $q->withoutGlobalScopes()->with('images');
        }])->where('client_id', $client->id)->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Seu carrinho está vazio.');
        }

        // Validação de limites de preço via backend
        $total = 0;
        foreach ($cartItems as $item) {
            $price = (float) $item->product->sale_price;
            if ($price > config('security.limits.max_product_price', 500)) {
                return back()->with('error', "Produto '{$item->product->name}' excede o valor máximo de R$ ".number_format(config('security.limits.max_product_price', 500), 2, ',', '.').'.');
            }
            $total += $price * $item->quantity;
        }

        if ($total > config('security.limits.max_cart_total', 1500)) {
            return back()->with('error', 'O valor total do carrinho excede o limite de R$ '.number_format(config('security.limits.max_cart_total', 1500), 2, ',', '.').'.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $cartItems->map(function ($item) {
            $unitAmount = (int) round((float) $item->product->sale_price * 100);
            return [
                'price_data' => [
                    'currency'    => 'brl',
                    'product_data' => [
                        'name'     => $item->product->name,
                        'metadata' => ['product_id' => $item->product->id],
                    ],
                    'unit_amount' => $unitAmount,
                ],
                'quantity' => $item->quantity,
            ];
        })->toArray();

        // Tipos de pagamento via Stripe
        $paymentMethodTypes = match ($validated['payment_method']) {
            'boleto' => ['boleto'],
            'pix'    => ['pix'],
            default  => ['card'],
        };

        $session = Session::create([
            'payment_method_types' => $paymentMethodTypes,
            'mode'                 => 'payment',
            'line_items'           => $lineItems,
            'success_url'          => route('checkout.success', [], true).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('checkout.cancel', [], true),
            'metadata'             => [
                'client_id'   => $client->id,
                'coupon_code' => $validated['coupon_code'] ?? null,
                'carrier_id'  => $validated['carrier_id'] ?? null,
            ],
        ]);

        // ── Persiste o pedido via CheckoutService (DB::transaction) ──
        //
        // O service cria Order + OrderItems (snapshots imutáveis) +
        // OrderLabel (snapshot do destinatário) e limpa o carrinho.
        $this->checkoutService->createOrderFromCart($client, [
            'stripe_session_id' => $session->id,
            'carrier_id'        => $validated['carrier_id'] ?? null,
            'coupon_code'       => $validated['coupon_code'] ?? null,
            'currency'          => 'brl',
        ]);

        return Inertia::location($session->url);
    }

    /**
     * Página de sucesso após redirecionamento do Stripe.
     *
     * Confirma o pagamento e atualiza o status da Order.
     * O carrinho já foi limpo pelo CheckoutService no momento da criação.
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                Order::where('stripe_session_id', $sessionId)
                    ->where('status', 'pending')
                    ->update(['status' => 'paid']);
            }
        }

        return Inertia::render('Client/CheckoutSuccess', [
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Página de cancelamento do checkout.
     */
    public function cancel()
    {
        return Inertia::render('Client/CheckoutCancel');
    }
}