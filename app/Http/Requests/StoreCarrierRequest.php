<?php

namespace App\Http\Requests;

use App\Models\Carrier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'doc_type' => ['required', 'string', 'in:CPF,CNPJ'],
            'document' => ['required', 'string', 'max:18'],
            'ie' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'zipcode' => ['nullable', 'string', 'max:9'],
            'contact1' => ['nullable', 'string', 'max:100'],
            'phone1' => ['nullable', 'string', 'max:20'],
            'contact2' => ['nullable', 'string', 'max:100'],
            'phone2' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $document = $this->input('document');
            if (empty($document)) return;
            $tenant = auth()->user()->tenant;
            if (!$tenant) return;
            $digits = preg_replace('/[.\-\/()\s]/', '', $document);
            $hash = hash('sha256', $digits);
            if (Carrier::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('document_hash', $hash)->exists()) {
                $validator->errors()->add('document', 'Já existe uma transportadora com este documento.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(strip_tags($this->name ?? '')),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da transportadora é obrigatório.',
            'document.required' => 'O CNPJ/CPF é obrigatório.',
        ];
    }
}