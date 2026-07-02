<?php

namespace App\Services;

use App\Models\Input;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportService
{
    /**
     * Relatório de insumos por estoque.
     *
     * @param int|null $perPage
     * @param int|null $threshold Quantidade mínima em estoque (filtra abaixo disso)
     */
    public function inputStockReport(int $perPage = 15, ?int $threshold = null): LengthAwarePaginator
    {
        $query = Input::query()->with('supplier');

        if ($threshold !== null) {
            $query->where('quantity', '<=', $threshold);
        }

        return $query->orderBy('quantity', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Relatório de produtos ativos.
     */
    public function activeProductsReport(int $perPage = 15): LengthAwarePaginator
    {
        return Product::where('is_active', true)
            ->with('tenant')
            ->orderBy('sale_price', 'desc')
            ->paginate($perPage);
    }

    /**
     * Relatório de vendas (orders).
     *
     * @param string|null $dateFrom Data inicial (Y-m-d)
     * @param string|null $dateTo   Data final (Y-m-d)
     */
    public function salesReport(int $perPage = 15, ?string $dateFrom = null, ?string $dateTo = null): LengthAwarePaginator
    {
        $query = Order::with(['client', 'product']);

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Totais agregados para o relatório de vendas.
     *
     * @return array{total_revenue: float, total_orders: int, avg_ticket: float}
     */
    public function salesTotals(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $query = Order::query();

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $totalRevenue = (float) $query->sum('price');
        $totalOrders = $query->count();
        $avgTicket = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_orders' => $totalOrders,
            'avg_ticket' => round($avgTicket, 2),
        ];
    }
}