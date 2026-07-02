<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCarrierRequest;
use App\Http\Requests\UpdateCarrierRequest;
use App\Models\Carrier;
use App\Services\CarrierService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarrierController extends Controller
{
    public function __construct(
        private CarrierService $carrierService,
    ) {}

    public function index(Request $request): Response
    {
        $carriers = Carrier::orderBy('name')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('Carriers/Index', [
            'carriers' => $carriers,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Carriers/Create');
    }

    public function store(StoreCarrierRequest $request)
    {
        $this->carrierService->create($request->validated());

        return redirect()->route('carriers.index')
            ->with('success', 'Transportadora criada com sucesso.');
    }

    public function edit(Carrier $carrier): Response
    {
        return Inertia::render('Carriers/Edit', [
            'carrier' => $carrier,
        ]);
    }

    public function update(UpdateCarrierRequest $request, Carrier $carrier)
    {
        $this->carrierService->update($carrier, $request->validated());

        return redirect()->route('carriers.index')
            ->with('success', 'Transportadora atualizada com sucesso.');
    }

    public function destroy(Carrier $carrier)
    {
        $this->carrierService->delete($carrier);

        return redirect()->route('carriers.index')
            ->with('success', 'Transportadora excluída com sucesso.');
    }
}