<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isStaff();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')
                    ->where(function ($query) {
                        return $query->where('tenant_id', auth()->user()->tenant_id);
                    })
                    ->ignore($this->route('product')),
            ],
            'description' => ['nullable', 'string'],
            'sale_price' => ['required', 'numeric', 'min:0.01'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.unique' => 'Já existe um produto com este nome.',
            'sale_price.required' => 'O preço de venda é obrigatório.',
            'sale_price.min' => 'O preço deve ser maior que zero.',
            'image.image' => 'O arquivo deve ser uma imagem.',
            'image.max' => 'A imagem deve ter no máximo 2MB.',
        ];
    }
}