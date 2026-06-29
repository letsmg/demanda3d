<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInputRequest;
use App\Http\Requests\UpdateInputRequest;
use App\Models\Input;
use App\Models\Supplier;
use App\Services\DashboardSearchService;
use App\Services\InputService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InputController extends Controller
{
    public function __construct(
        private InputService $inputService,
        private DashboardSearchService $searchService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->get('search');

        if ($search && strlen($search) >= 3 && auth()->user()->tenant_id) {
            $inputs = $this->searchService->search('inputs', $search, (string) auth()->user()->tenant_id);
        } else {
            $inputs = Input::orderBy('purchase_date', 'desc')
                ->paginate($request->get('per_page', 10))
                ->withQueryString();
        }

        return Inertia::render('Inputs/Index', [
            'inputs' => $inputs,
        ]);
    }

    public function create(): Response
    {
        $suppliers = Supplier::orderBy('name')->get();

        return Inertia::render('Inputs/Create', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(StoreInputRequest $request)
    {
        $this->inputService->create($request->validated());

        return redirect()->route('inputs.index')
            ->with('success', 'Insumo criado com sucesso.');
    }

    public function edit(Input $input): Response
    {
        $suppliers = Supplier::orderBy('name')->get();

        return Inertia::render('Inputs/Edit', [
            'input' => $input,
            'suppliers' => $suppliers,
        ]);
    }

    public function update(UpdateInputRequest $request, Input $input)
    {
        $this->inputService->update($input, $request->validated());

        return redirect()->route('inputs.index')
            ->with('success', 'Insumo atualizado com sucesso.');
    }

    public function destroy(Input $input)
    {
        $this->inputService->delete($input);

        return redirect()->route('inputs.index')
            ->with('success', 'Insumo excluído com sucesso.');
    }
}