<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'string', 'max:18'],
            'contact' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Validação pós-regras: verifica unicidade do documento via hash
     * ignorando o próprio fornecedor sendo editado.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $document = $this->input('document');

            if (empty($document)) {
                return;
            }

            // Obtém tenant via relação HasOne
            $tenant = auth()->user()->tenant;

            if (!$tenant) {
                $validator->errors()->add('document', 'Erro interno: tenant não encontrado para o usuário.');

                return;
            }

            $tenantId = $tenant->id;
            $digits = preg_replace('/[.\-\/()\s]/', '', $document);
            $hash = hash('sha256', $digits);

            $supplier = $this->route('supplier');

            $query = Supplier::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('document_hash', $hash);

            if ($supplier) {
                $query->where('id', '!=', $supplier->id);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    'document',
                    'Já existe outro fornecedor com este documento cadastrado.',
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(strip_tags($this->name ?? '')),
            'contact' => trim(strip_tags($this->contact ?? '')),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do fornecedor é obrigatório.',
            'document.required' => 'O CNPJ/CPF é obrigatório.',
            'contact.required' => 'O contato é obrigatório.',
        ];
    }
}