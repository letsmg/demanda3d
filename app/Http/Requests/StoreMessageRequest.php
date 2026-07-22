<?php

namespace App\Http\Requests;

use App\Services\MessageSanitizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // A autorização é delegada ao Controller/Policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'min:1',
                'max:5000',
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Aplica trim() e strip_tags() para sanitização básica (XSS prevention).
     *
     * O bloqueio de PII (MessageSanitizer) é aplicado na camada de Service
     * (ThreadService/DisputeService), NÃO aqui na FormRequest, para garantir
     * que a validação de PII aconteça em TODOS os fluxos de mensagens,
     * incluindo APIs internas que não passam por FormRequests.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('content') && is_string($this->input('content'))) {
            $this->merge([
                'content' => trim(strip_tags($this->input('content'))),
            ]);
        }
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'O conteúdo da mensagem é obrigatório.',
            'content.min'      => 'A mensagem deve ter pelo menos 1 caractere.',
            'content.max'      => 'A mensagem não pode exceder 5000 caracteres.',
        ];
    }
}