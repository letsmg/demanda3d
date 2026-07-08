<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
                Rule::unique('products', 'name')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                }),
            ],
            'description' => ['nullable', 'string'],
            'sale_price' => ['required', 'numeric', 'min:0.01'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array', 'max:3'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'sale_price.required' => 'O preço de venda é obrigatório.',
            'sale_price.min' => 'O preço deve ser maior que zero.',
            'images.*.image' => 'O arquivo deve ser uma imagem.',
            'images.*.max' => 'A imagem deve ter no máximo 2MB.',
            'images.max' => 'O produto pode ter no máximo 5 imagens.',
            'categories.max' => 'O produto pode ter no máximo 3 categorias.',
            'categories.*.exists' => 'Uma ou mais categorias selecionadas são inválidas.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(strip_tags($this->name ?? '')),
        ]);
    }
}