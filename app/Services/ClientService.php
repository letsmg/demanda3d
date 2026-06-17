<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Pagination\Paginator;

class ClientService
{
    public function list(int $perPage = 15): Paginator
    {
        return Client::paginate($perPage);
    }

    public function findById(int $id): Client
    {
        return Client::findOrFail($id);
    }

    public function create(array $data): Client
    {
        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client;
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }
}
