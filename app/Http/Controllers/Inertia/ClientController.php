<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use App\Services\DashboardSearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(
        private ClientService $clientService,
        private DashboardSearchService $searchService,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->get('search');

        if ($search && strlen($search) >= 3 && auth()->user()->tenant_id) {
            $results = $this->searchService->search('clients', $search, (string) auth()->user()->tenant_id);
            $clients = collect($results);
        } else {
            $clients = Client::orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 10))
                ->withQueryString();
        }

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create');
    }

    public function store(StoreClientRequest $request)
    {
        $this->clientService->create($request->validated());

        return redirect()->route('clients.index')
            ->with('success', 'Cliente criado com sucesso.');
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->clientService->update($client, $request->validated());

        return redirect()->route('clients.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Client $client)
    {
        $this->clientService->delete($client);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente excluído com sucesso.');
    }
}