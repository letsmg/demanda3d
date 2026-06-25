<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    /**
     * Dashboard de relatórios com resumo geral.
     */
    public function index(): Response
    {
        $totals = $this->reportService->salesTotals();

        return Inertia::render('Reports/Index', [
            'totals' => $totals,
        ]);
    }

    /**
     * Relatório de insumos por estoque.
     */
    public function inputs(Request $request): Response
    {
        $threshold = $request->get('threshold') !== null
            ? (int) $request->get('threshold')
            : null;

        $inputs = $this->reportService->inputStockReport(
            $request->get('per_page', 15),
            $threshold
        );

        return Inertia::render('Reports/Inputs', [
            'inputs' => $inputs,
            'filters' => [
                'threshold' => $request->get('threshold'),
            ],
        ]);
    }

    /**
     * Relatório de produtos ativos.
     */
    public function products(): Response
    {
        $products = $this->reportService->activeProductsReport();

        return Inertia::render('Reports/Products', [
            'products' => $products,
        ]);
    }

    /**
     * Relatório de vendas.
     */
    public function sales(Request $request): Response
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $sales = $this->reportService->salesReport(
            $request->get('per_page', 15),
            $dateFrom,
            $dateTo
        );

        $totals = $this->reportService->salesTotals($dateFrom, $dateTo);

        return Inertia::render('Reports/Sales', [
            'sales' => $sales,
            'totals' => $totals,
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}