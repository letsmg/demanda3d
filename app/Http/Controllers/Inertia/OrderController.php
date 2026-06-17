<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): Response
    {
        $orders = Order::with('client')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function create(): Response
    {
        $clients = Client::orderBy('name')->get(['id', 'name', 'doc']);

        return Inertia::render('Orders/Create', [
            'clients' => $clients,
        ]);
    }

    public function store(StoreOrderRequest $request)
    {
        $this->orderService->create($request->validated());

        return redirect()->route('orders.index')
            ->with('success', 'Pedido criado com sucesso.');
    }

    public function edit(Order $order): Response
    {
        $order->load('client');
        $clients = Client::orderBy('name')->get(['id', 'name', 'doc']);

        return Inertia::render('Orders/Edit', [
            'order' => $order,
            'clients' => $clients,
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $this->orderService->update($order, $request->validated());

        return redirect()->route('orders.index')
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function destroy(Order $order)
    {
        $this->orderService->delete($order);

        return redirect()->route('orders.index')
            ->with('success', 'Pedido excluído com sucesso.');
    }
}