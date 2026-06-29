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
            'meta_title' => ['nullable', 'string', 'max:120'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'og_image' => ['nullable', 'url', 'max:255'],
            'categorias' => ['nullable', 'array', 'max:3'],
            'categorias.*' => ['integer', 'exists:categorias,id'],
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
            'categorias.max' => 'O produto pode ter no máximo 3 categorias.',
            'categorias.*.exists' => 'Uma ou mais categorias selecionadas são inválidas.',
            'meta_title.max' => 'O meta título deve ter no máximo 120 caracteres.',
            'meta_description.max' => 'A meta descrição deve ter no máximo 320 caracteres.',
        ];
    }

    /**
     * Prepara os dados para validação, aplicando trim e strip_tags.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim(strip_tags($this->name ?? '')),
            'meta_title' => $this->meta_title ? trim(strip_tags($this->meta_title)) : null,
            'meta_description' => $this->meta_description ? trim(strip_tags($this->meta_description)) : null,
        ]);
    }
}
