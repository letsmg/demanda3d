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
        $metaKeywords = $this->meta_keywords
            ?: SeoSetting::getValue('meta_keywords_default', 'impressão 3D, marketplace, demanda 3D');
        $canonicalUrl = route('store.detail', ['slug' => $this->slug]);
        $ogImage = SeoSetting::getValue('og_image_default', asset('images/og-default.jpg'));
        // schema_markup and google_tag_manager are raw code - no fallback needed
        $schemaMarkup = $this->schema_markup;
        $googleTagManager = $this->google_tag_manager;

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
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'is_adult' => $cat->is_adult,
                ]);
            }),

            // SEO
            'seo' => [
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
                'canonical_url' => $canonicalUrl,
                'og_image' => $ogImage,
                'h1_text' => $this->name,
                'schema_markup' => $schemaMarkup,
                'google_tag_manager' => $googleTagManager,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}