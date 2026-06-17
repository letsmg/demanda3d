<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'doc' => ['required', 'string', 'max:20', 'unique:clients,doc'],
            'address' => ['required', 'string', 'max:255'],
            'number' => ['required', 'string', 'max:20'],
            'state' => ['required', 'string', 'size:2'],
            'zipcode' => ['required', 'string', 'regex:/^\d{5}-?\d{3}$/'],
            'city' => ['required', 'string', 'max:100'],
            'phone1' => ['required', 'string', 'max:20'],
            'phone2' => ['required', 'string', 'max:20'],
            'contact1' => ['nullable', 'string', 'max:100'],
            'contact2' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do cliente é obrigatório',
            'doc.unique' => 'Este documento já está registrado',
            'zipcode.regex' => 'O CEP deve estar no formato 12345-678',
        ];
    }
}
