<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Input;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): Response
    {
        // 1. Contagens simples (otimizadas)
        $clientsCount = Client::count();
        $ordersCount = Order::count();
        $inputsCount = Input::count();

        // 2. Consultas recentes
        $recentOrders = Order::with('client')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentInputs = Input::orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        // 3. Métricas de Receita (Filtradas diretamente no banco, sem carregar todos os registros)
        $monthlyRevenue = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('price');

        $monthlyOrdersCount = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        $totalRevenue = Order::sum('price');
        $avgOrderValue = $ordersCount > 0 ? $totalRevenue / $ordersCount : 0;

        // 4. Entregas Pendentes (Filtro eficiente no banco)
        // Certifique-se de que 'delivery_date' esteja no $casts do Model Order como 'datetime'
        $pendingDeliveries = Order::whereNotNull('delivery_date')
            ->where('delivery_date', '>', Carbon::now())
            ->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'clients_count' => $clientsCount,
                'orders_count' => $ordersCount,
                'inputs_count' => $inputsCount,
                'monthly_revenue' => (float) $monthlyRevenue,
                'monthly_orders_count' => $monthlyOrdersCount,
                'pending_deliveries' => $pendingDeliveries,
                'total_revenue' => (float) $totalRevenue,
                'avg_order_value' => (float) $avgOrderValue,
                'recent_orders' => $recentOrders,
                'recent_inputs' => $recentInputs,
            ],
        ]);
    }
}