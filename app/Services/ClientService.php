<?php

namespace App\Services;

use App\Enums\DocumentType;
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

        // Build display_name if not provided
        $data = $this->buildDisplayName($data);

        // Auto-detect doc_type if not provided
        if (isset($data['doc']) && empty($data['doc_type'])) {
            $data['doc_type'] = DocumentType::detect($data['doc'])->value;
        }

        // Encrypt sensitive fields
        $data = $this->encryptSensitiveFields($data);

        // Remove plain text fields — only encrypted+hash columns exist
        $data = $this->stripPlainTextFields($data);

        return Client::create($data);
    }

    public function update(Client $client, array $data): Client
    {
        // Build display_name if not provided
        $data = $this->buildDisplayName($data);

        // Auto-detect doc_type if not provided
        if (isset($data['doc']) && empty($data['doc_type'])) {
            $data['doc_type'] = DocumentType::detect($data['doc'])->value;
        }

        // Encrypt sensitive fields
        $data = $this->encryptSensitiveFields($data);

        // Remove plain text fields — only encrypted+hash columns exist
        $data = $this->stripPlainTextFields($data);

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
        $digits = DocumentValidationService::digitsOnly($doc);
        $hash = EncryptionService::hash($digits);

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
     * Apply LGPD parity structure: build *_hash and *_encrypted for all sensitive fields.
     */
    private function encryptSensitiveFields(array $data): array
    {
        foreach (['first_name', 'last_name'] as $field) {
            if (isset($data[$field])) {
                $data = EncryptionService::buildEncryptedFields($data, $field);
            }
        }

        if (isset($data['doc'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'doc');
        }

        foreach (['address', 'number', 'state', 'zipcode', 'city'] as $field) {
            if (isset($data[$field])) {
                $data = EncryptionService::buildEncryptedFields($data, $field);
            }
        }

        foreach (['phone1', 'phone2'] as $field) {
            if (isset($data[$field])) {
                $data = EncryptionService::buildEncryptedFields($data, $field);
            }
        }

        foreach (['contact1', 'contact2'] as $field) {
            if (isset($data[$field])) {
                $data = EncryptionService::buildEncryptedFields($data, $field);
            }
        }

        return $data;
    }

    /**
     * Remove plain text fields — only *_encrypted and *_hash columns exist in DB.
     */
    private function stripPlainTextFields(array $data): array
    {
        $plainFields = ['first_name', 'last_name', 'doc', 'address', 'number', 'state', 'zipcode', 'city', 'phone1', 'phone2', 'contact1', 'contact2'];

        foreach ($plainFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}
