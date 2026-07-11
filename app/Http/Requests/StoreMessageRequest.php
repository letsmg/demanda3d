<?php

namespace App\Http\Requests;

use App\Rules\NoContactDataRule;
use App\Rules\NoOffensiveContentRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMessageRequest extends FormRequest
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
            'thread_id' => ['required', 'exists:threads,id'],
            'content' => [
                'required',
                'string',
                'min:1',
                'max:5000',
                new NoContactDataRule,
                new NoOffensiveContentRule,
            ],
            'sender_type' => ['required', Rule::in(['staff', 'client'])],
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
            'thread_id.required' => 'A thread da conversa é obrigatória.',
            'thread_id.exists' => 'A thread informada não existe.',
            'content.required' => 'O conteúdo da mensagem é obrigatório.',
            'content.max' => 'A mensagem não pode ter mais que 5000 caracteres.',
            'sender_type.required' => 'O tipo de remetente é obrigatório.',
            'sender_type.in' => 'O tipo de remetente deve ser staff ou client.',
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
            'thread_id' => 'thread da conversa',
            'content' => 'conteúdo da mensagem',
            'sender_type' => 'tipo de remetente',
        ];
    }
}