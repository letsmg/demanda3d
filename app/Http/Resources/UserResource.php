<?php
// Copyright (c) 2026 Luiz Eduardo T. Silva. Todos os direitos reservados.

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var \App\Models\User $this */
        return [
            'id' => $this->id,
            'email' => $this->email,
            'display_name' => $this->display_name,
            'access_level' => $this->access_level,
            'pode_ver_adultos' => $this->canAccessAdultContent(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}