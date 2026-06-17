<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin() || $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', 'exists:clients,id'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['required', 'date', 'after_or_equal:order_date'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'contracted_description' => ['required', 'string', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.exists' => 'O cliente selecionado não existe',
            'delivery_date.after_or_equal' => 'A data de entrega deve ser igual ou posterior à data do pedido',
            'price.min' => 'O preço deve ser maior que zero',
            'contracted_description.min' => 'A descrição deve ter no mínimo 10 caracteres',
        ];
    }
}
