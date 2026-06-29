<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Resources;

use App\Models\SeoSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        // SEO fallback: busca na tabela seo_settings, depois config/app.php, depois fallback estático
        $metaTitle = $this->meta_title
            ?: SeoSetting::getValue('meta_title_default', config('app.name'));
        $metaDescription = $this->meta_description
            ?: SeoSetting::getValue('meta_description_default', config('app.seo.default_description', 'Marketplace de impressão 3D sob demanda.'));
        $canonicalUrl = $this->canonical_url
            ?: SeoSetting::getValue('canonical_url_default', route('api.produtos.show', ['slug' => $this->slug]));
        $ogImage = $this->og_image
            ?: SeoSetting::getValue('og_image_default', asset('images/og-default.jpg'));

        return [
            'id' => $this->id,
            'tenant_id' => $this->when($user && $user->isStaff(), $this->tenant_id),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'height' => $this->height,
            'width' => $this->width,
            'approximate_weight' => $this->approximate_weight,
            'waste_weight' => $this->when($user && $user->isStaff(), $this->waste_weight),
            'material_type' => $this->material_type,
            'print_time' => $this->print_time,
            'pieces_produced' => $this->pieces_produced,
            'maintenance_fee' => $this->when($user && $user->isStaff(), $this->maintenance_fee),
            'painting_time' => $this->painting_time,
            'painting_material' => $this->painting_material,
            'painting_cost' => $this->when($user && $user->isStaff(), $this->painting_cost),
            'extras_cost' => $this->when($user && $user->isStaff(), $this->extras_cost),
            'approximate_cost' => $this->when($user && $user->isStaff(), $this->approximate_cost),
            'sale_price' => $this->sale_price,
            'is_active' => $this->is_active,
            'has_adult_content' => $this->hasAdultContent(),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(fn ($img) => [
                    'id' => $img->id,
                    'url' => url('storage/' . $img->path),
                    'order' => $img->order,
                ]);
            }),
            'categorias' => $this->whenLoaded('categorias', function () {
                return $this->categorias->map(fn ($cat) => [
                    'id' => $cat->id,
                    'nome' => $cat->nome,
                    'slug' => $cat->slug,
                    'maior_de_idade' => $cat->maior_de_idade,
                ]);
            }),

            // SEO
            'seo' => [
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'canonical_url' => $canonicalUrl,
                'og_image' => $ogImage,
                'h1_text' => $this->name,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}