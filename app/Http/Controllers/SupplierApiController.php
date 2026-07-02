<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierApiController extends Controller
{
    public function __construct(
        private SupplierService $supplierService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::orderBy('name')
            ->paginate($request->get('per_page', 10));

        return response()->json($suppliers);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json($supplier);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = $this->supplierService->create($request->validated());

        return response()->json($supplier, 201);
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $this->supplierService->update($supplier, $request->validated());

        return response()->json($supplier);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->supplierService->delete($supplier);

        return response()->json(null, 204);
    }
}