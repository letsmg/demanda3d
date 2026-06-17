<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use App\Services\ClientService;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    public function __construct(private ClientService $clientService)
    {
        $this->middleware('auth:sanctum');
        $this->middleware('staff.only')->except('show', 'index');
        $this->middleware('admin.only')->only('destroy');
    }

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Client::class);
        $clients = $this->clientService->list();

        return response()->json($clients);
    }

    public function show(Client $client): JsonResponse
    {
        $this->authorize('view', $client);

        return response()->json($client);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        $this->authorize('create', Client::class);
        $client = $this->clientService->create($request->validated());

        return response()->json($client, 201);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        $this->authorize('update', $client);
        $client = $this->clientService->update($client, $request->validated());

        return response()->json($client);
    }

    public function destroy(Client $client): JsonResponse
    {
        $this->authorize('delete', $client);
        $this->clientService->delete($client);

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
