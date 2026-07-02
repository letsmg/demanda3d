<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Services;

use App\Models\Carrier;

class CarrierService
{
    public function create(array $data): Carrier
    {
        $data['tenant_id'] = auth()->user()->tenant->id;
        $data = $this->encryptSensitiveFields($data);

        return Carrier::create($data);
    }

    public function update(Carrier $carrier, array $data): Carrier
    {
        $data = $this->encryptSensitiveFields($data);
        $carrier->update($data);

        return $carrier;
    }

    public function delete(Carrier $carrier): bool
    {
        return $carrier->delete();
    }

    private function encryptSensitiveFields(array $data): array
    {
        if (isset($data['document'])) {
            $data = EncryptionService::buildEncryptedFields($data, 'document');
        }
        foreach (['address', 'number', 'district', 'city'] as $field) {
            if (isset($data[$field])) {
                $data = EncryptionService::buildEncryptedFields($data, $field);
            }
        }
        foreach (['contact1', 'phone1', 'contact2', 'phone2'] as $field) {
            if (isset($data[$field])) {
                $data = EncryptionService::buildEncryptedFields($data, $field);
            }
        }

        return $data;
    }
}