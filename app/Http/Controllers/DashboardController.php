<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Input;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $clientsCount = Client::count();
        $ordersCount = Order::count();
        $inputsCount = Input::count();

        $recentOrders = Order::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentInputs = Input::orderBy('dt_buy', 'desc')
            ->take(3)
            ->get();

        $allOrders = Order::all();
        $monthlyOrders = $allOrders->filter(function ($order) {
            return $order->created_at->isCurrentMonth();
        });

        $monthlyRevenue = $monthlyOrders->sum('price');
        $totalRevenue = $allOrders->sum('price');
        $avgOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0;

        $pendingDeliveries = $allOrders->filter(function ($order) {
            return $order->delivery_date && $order->delivery_date->isFuture();
        })->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'clients_count' => $clientsCount,
                'orders_count' => $ordersCount,
                'inputs_count' => $inputsCount,
                'monthly_revenue' => (float) $monthlyRevenue,
                'monthly_orders_count' => $monthlyOrders->count(),
                'pending_deliveries' => $pendingDeliveries,
                'total_revenue' => (float) $totalRevenue,
                'avg_order_value' => (float) $avgOrderValue,
                'recent_orders' => $recentOrders,
                'recent_inputs' => $recentInputs,
            ],
        ]);
    }
}