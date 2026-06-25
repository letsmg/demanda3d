<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInputRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'description' => ['required', 'string', 'max:255'],
            'brand' => ['required', 'string', 'max:255'],
            'purchase_date' => ['required', 'date'],
            'quantity' => ['required', 'integer', 'min:1'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
            'cost_value' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'O fornecedor é obrigatório',
            'description.required' => 'A descrição do insumo é obrigatória',
            'brand.required' => 'A marca é obrigatória',
            'purchase_date.required' => 'A data de compra é obrigatória',
            'quantity.required' => 'A quantidade é obrigatória',
            'shipping_cost.required' => 'O valor do frete é obrigatório',
            'cost_value.required' => 'O valor pago é obrigatório',
        ];
    }
}