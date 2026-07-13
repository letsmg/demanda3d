<?php

namespace App\Http\Requests;

use App\Rules\NoContactDataRule;
use App\Rules\NoOffensiveContentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDisputeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Autorização delegada à Policy no Controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => ['nullable', 'exists:orders,id'],
            'reason' => ['required', Rule::in(['fraud', 'fake_product', 'offensive', 'not_delivered'])],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000',
                new NoContactDataRule,
                new NoOffensiveContentRule,
            ],
        ];
    }

    /**
     * Mensagens de erro customizadas (pt-BR).
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'order_id.exists' => 'O pedido informado não existe.',
            'reason.required' => 'O motivo da disputa é obrigatório.',
            'reason.in' => 'O motivo deve ser: fraude, produto falso, conteúdo ofensivo ou não entregue.',
            'description.required' => 'A descrição do problema é obrigatória.',
            'description.min' => 'A descrição deve ter pelo menos 10 caracteres.',
            'description.max' => 'A descrição não pode ter mais que 10000 caracteres.',
        ];
    }

    /**
     * Nomes dos atributos traduzidos.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'order_id' => 'pedido',
            'reason' => 'motivo da disputa',
            'description' => 'descrição do problema',
        ];
    }
}