<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use App\Services\DocumentValidationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isPartner();
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'display_name' => ['nullable', 'string', 'max:255'],
            'doc_type' => ['nullable', 'string', 'in:CPF,CNPJ'],
            'doc' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'state' => ['required', 'string', 'size:2'],
            'zipcode' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'city' => ['required', 'string', 'max:100'],
            'phone1' => ['required', 'string', 'max:20'],
            'phone2' => ['nullable', 'string', 'max:20'],
            'contact1' => ['nullable', 'string', 'max:100'],
            'contact2' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Configure the validator instance with after-validation hooks.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $doc = $this->input('doc');
            $docType = $this->input('doc_type');

            if (empty($doc)) {
                return;
            }

            // Auto-detect document type if not provided
            $type = $docType ? DocumentType::from($docType) : null;

            // Validate document digits
            if (!DocumentValidationService::validate($doc, $type)) {
                $detected = $type ?? DocumentType::detect($doc);
                $label = $detected === DocumentType::CPF ? 'CPF' : 'CNPJ';
                $validator->errors()->add('doc', "O {$label} informado é inválido.");
            }

            // Check uniqueness only after validation passes (hash-based)
            $digits = DocumentValidationService::digitsOnly($doc);
            $hash = hash('sha256', $digits);
            $exists = \App\Models\Client::byDocHash($hash)->exists();

            if ($exists) {
                $validator->errors()->add('doc', 'Este documento já está registrado.');
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $doc = $this->input('doc');
        $docType = $this->input('doc_type');

        if ($doc && !$docType) {
            $this->merge([
                'doc_type' => DocumentType::detect($doc)->value,
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'O primeiro nome do cliente é obrigatório.',
            'last_name.required' => 'O sobrenome do cliente é obrigatório.',
            'doc_type.in' => 'O tipo de documento deve ser CPF ou CNPJ.',
            'zipcode.regex' => 'O CEP deve estar no formato 12345-678.',
        ];
    }
}
