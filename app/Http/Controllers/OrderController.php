<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService)
    {
        $this->middleware('staff.only')->except('show', 'index');
        $this->middleware('admin.only')->only('destroy');
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderService->list((int) $request->get('per_page', 15));

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        return response()->json($order->load('client'));
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);
        $order = $this->orderService->create($request->validated());

        return response()->json($order->load('client'), 201);
    }

    public function update(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);
        $order = $this->orderService->update($order, $request->validated());

        return response()->json($order->load('client'));
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);
        $this->orderService->delete($order);

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function byClient(int $clientId): JsonResponse
    {
        $this->authorize('viewAny', Order::class);
        $orders = $this->orderService->findByClient($clientId);

        return response()->json($orders);
    }
}
