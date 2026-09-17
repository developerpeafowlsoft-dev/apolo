<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = request()->header('accept-language') ?? 'en';
        $translation = $lang != 'en' ? $this->translations()?->where('lang', $lang)->first() : null;

        return [
            'id' => $this->id ?? null,
            'name' => $translation ? $translation->name : ($this->name ?? null),
            'thumbnail' => $this->thumbnail ?? null,
            'inward_products_count' => (int) ($this->inward_products_count ?? 0),
            'online_products_count' => (int) ($this->online_products_count ?? 0),
        ];
    }
}
