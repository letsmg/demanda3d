<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'filaments' => ['required', 'string', 'max:255'],
            'energy' => ['required', 'numeric', 'min:0'],
            'dt_buy' => ['required', 'date'],
            'cost_buy' => ['required', 'numeric', 'min:0'],
            'purge' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'filaments.required' => 'O tipo de filamento é obrigatório',
            'energy.required' => 'O consumo de energia é obrigatório',
            'dt_buy.required' => 'A data de compra é obrigatória',
            'cost_buy.required' => 'O custo de compra é obrigatório',
        ];
    }
}