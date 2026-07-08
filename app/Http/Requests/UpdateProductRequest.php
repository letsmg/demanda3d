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
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'images_order' => ['nullable', 'array'],
            'images_order.*' => ['integer', 'exists:product_images,id'],
            'images_delete' => ['nullable', 'array'],
            'images_delete.*' => ['integer', 'exists:product_images,id'],
            'is_active' => ['nullable', 'boolean'],
            'categories' => ['nullable', 'array', 'max:3'],
            'categories.*' => ['integer', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'name.unique' => 'Já existe um produto com este nome.',
            'sale_price.required' => 'O preço de venda é obrigatório.',
            'sale_price.min' => 'O preço deve ser maior que zero.',
            'images.*.image' => 'O arquivo deve ser uma imagem.',
            'images.*.max' => 'A imagem deve ter no máximo 2MB.',
            'images.max' => 'O produto pode ter no máximo 5 imagens.',
            'images_order.*.exists' => 'Uma ou mais imagens na ordenação são inválidas.',
            'images_delete.*.exists' => 'Uma ou mais imagens para exclusão são inválidas.',
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