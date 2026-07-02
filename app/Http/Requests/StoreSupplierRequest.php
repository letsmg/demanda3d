<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSupplierRequest extends FormRequest
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
     * ANTES do banco, evitando UniqueConstraintViolationException.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $document = $this->input('document');

            if (empty($document)) {
                return;
            }

            // Obtém tenant via relação HasOne (mesmo padrão do SupplierService)
            $tenant = auth()->user()->tenant;

            if (!$tenant) {
                $validator->errors()->add('document', 'Erro interno: tenant não encontrado para o usuário.');

                return;
            }

            $digits = preg_replace('/[.\-\/()\s]/', '', $document);
            $hash = hash('sha256', $digits);

            $exists = Supplier::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('document_hash', $hash)
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'document',
                    'Já existe um fornecedor com este documento cadastrado.',
                );
            }
        });
    }

    /**
     * Prepara os dados antes da validação: sanitiza name e contact.
     * NOTA: document NÃO é sanitizado pois contém pontuação (./-) de CPF/CNPJ.
     */
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