<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    public function __construct(private ClientService $clientService) {}

    public function index(Request $request): Response
    {
        $clients = Client::orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
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