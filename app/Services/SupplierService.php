<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\Crypt;

class SupplierService
{
    public function create(array $data): Supplier
    {
        $data['tenant_id'] = auth()->user()->tenant->id;

        // Criptografar campos sensíveis (LGPD)
        if (isset($data['document'])) {
            $data['document_hash'] = hash('sha256', preg_replace('/[.\-\/()\s]/', '', $data['document']));
            $data['document_encrypted'] = Crypt::encryptString($data['document']);
            unset($data['document']);
        }

        if (isset($data['contact'])) {
            $data['contact_encrypted'] = Crypt::encryptString($data['contact']);
            unset($data['contact']);
        }

        return Supplier::create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        if (isset($data['document'])) {
            $data['document_hash'] = hash('sha256', preg_replace('/[.\-\/()\s]/', '', $data['document']));
            $data['document_encrypted'] = Crypt::encryptString($data['document']);
            unset($data['document']);
        }

        if (isset($data['contact'])) {
            $data['contact_encrypted'] = Crypt::encryptString($data['contact']);
            unset($data['contact']);
        }

        $supplier->update($data);

        return $supplier;
    }

    public function delete(Supplier $supplier): bool
    {
        return $supplier->delete();
    }
}