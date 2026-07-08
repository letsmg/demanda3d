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

            // SEO — 100% dinâmico via accessors no Model Product
            'seo' => [
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'canonical_url' => $this->canonical_url,
                'og_image' => $this->og_image,
                'h1_text' => $this->name,
                'schema_markup' => $this->schema_markup,
                'google_tag_manager' => $this->google_tag_manager,
            ],

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}