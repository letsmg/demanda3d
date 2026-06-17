<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Pagination\Paginator;

class OrderService
{
    public function list(int $perPage = 15): Paginator
    {
        return Order::with('client')->paginate($perPage);
    }

    public function findById(int $id): Order
    {
        return Order::with('client')->findOrFail($id);
    }

    public function findByClient(int $clientId, int $perPage = 15): Paginator
    {
        return Order::where('client_id', $clientId)
            ->with('client')
            ->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);

        return $order;
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
