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
        $data['tenant_id'] = auth()->user()->tenant->id;

        // Sanitize inputs
        $data = $this->sanitizeData($data);

        // Encrypt sensitive fields
        $data = $this->encryptSensitiveFields($data);

        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        // Sanitize inputs
        $data = $this->sanitizeData($data);

        // Encrypt sensitive fields
        $data = $this->encryptSensitiveFields($data);

        $client->update($data);

        return $client;
    }

    public function delete(Client $client): bool
    {
        return $client->delete();
    }

    /**
     * Search clients by document (using hash).
     */
    public function findByDoc(string $doc): ?Client
    {
        $hash = EncryptionService::hash($doc);

        if ($hash === null) {
            return null;
        }

        return Client::byDocHash($hash)->first();
    }

    /**
     * Search clients by phone (using hash).
     */
    public function findByPhone(string $phone): ?Client
    {
        $hash = EncryptionService::hash($phone);

        if ($hash === null) {
            return null;
        }

        return Client::byPhoneHash($hash)->first();
    }

    /**
     * Build display_name if not provided.
     */
    private function buildDisplayName(array $data): array
    {
        if (empty($data['display_name']) && ! empty($data['first_name'])) {
            $lastName = $data['last_name'] ?? '';
            $data['display_name'] = trim($data['first_name'] . ' ' . $lastName);
        }

        return $data;
    }

    /**
     * Sanitize all string fields in the data.
     */
    private function sanitizeData(array $data): array
    {
        $sensitiveFields = ['first_name', 'last_name', 'display_name', 'name', 'address', 'number', 'state', 'city', 'district', 'contact1', 'contact2'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = EncryptionService::sanitize($data[$field]);
            }
        }

        // Sanitize but preserve formatting for doc and phone (they will be encrypted)
        foreach (['doc', 'phone1', 'phone2'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim(strip_tags($data[$field]));
            }
        }

        return $data;
    }

    /**
     * Encrypt sensitive fields and generate hashes for searching.
     */
    private function encryptSensitiveFields(array $data): array
    {
        // Encrypt document
        if (isset($data['doc'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'doc');
        }

        // Encrypt phone numbers
        if (isset($data['phone1'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'phone1');
        }

        if (isset($data['phone2'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'phone2');
        }

        return $data;
    }
}