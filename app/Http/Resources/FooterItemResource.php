<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FooterItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $generaleSetting = generaleSetting('setting');
        $title = $this->title;

        if ($this->type == 'phone') {
            $title = $generaleSetting?->mobile ?? $generaleSetting?->footer_phone ?? $this->title;
        } elseif ($this->type == 'email') {
            $title = $generaleSetting?->email ?? $generaleSetting?->footer_email ?? config('mail.from.address') ?? $this->title;
        }

        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $title,
            'url' => $this->url,
            'target' => $this->target,
        ];
    }
}
