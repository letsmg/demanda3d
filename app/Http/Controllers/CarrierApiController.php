<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarrierRequest;
use App\Http\Requests\UpdateCarrierRequest;
use App\Models\Carrier;
use App\Services\CarrierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarrierApiController extends Controller
{
    public function __construct(
        private CarrierService $carrierService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(Carrier::orderBy('fantasy_name')->paginate($request->get('per_page', 10)));
    }

    public function show(Carrier $carrier): JsonResponse
    {
        return response()->json($carrier);
    }

    public function store(StoreCarrierRequest $request): JsonResponse
    {
        return response()->json($this->carrierService->create($request->validated()), 201);
    }

    public function update(UpdateCarrierRequest $request, Carrier $carrier): JsonResponse
    {
        return response()->json($this->carrierService->update($carrier, $request->validated()));
    }

    public function destroy(Carrier $carrier): JsonResponse
    {
        return response()->json(null, 204);
    }
}