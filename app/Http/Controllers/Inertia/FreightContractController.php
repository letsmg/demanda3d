<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFreightContractRequest;
use App\Http\Requests\UpdateFreightContractRequest;
use App\Models\Carrier;
use App\Models\FreightContract;
use App\Services\FreightContractService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FreightContractController extends Controller
{
    public function __construct(
        private FreightContractService $service,
    ) {}

    public function index(Request $request): Response
    {
        $contracts = FreightContract::with('carrier')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('FreightContracts/Index', [
            'contracts' => $contracts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('FreightContracts/Create', [
            'carriers' => Carrier::orderBy('name')->get(),
        ]);
    }

    public function store(StoreFreightContractRequest $request)
    {
        $this->service->create($request->validated());

        return redirect()->route('freight-contracts.index')
            ->with('success', 'Contrato de frete criado com sucesso.');
    }

    public function edit(FreightContract $contract): Response
    {
        return Inertia::render('FreightContracts/Edit', [
            'contract' => $contract,
            'carriers' => Carrier::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateFreightContractRequest $request, FreightContract $contract)
    {
        $this->service->update($contract, $request->validated());

        return redirect()->route('freight-contracts.index')
            ->with('success', 'Contrato de frete atualizado com sucesso.');
    }

    public function destroy(FreightContract $contract)
    {
        $this->service->delete($contract);

        return redirect()->route('freight-contracts.index')
            ->with('success', 'Contrato de frete excluído com sucesso.');
    }
}