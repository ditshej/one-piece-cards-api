<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pack_id' => $this->pack_id,
            'name' => $this->name,
            'rarity' => $this->rarity,
            'category' => $this->category,
            'colors' => $this->colors,
            'cost' => $this->cost,
            'power' => $this->power,
            'counter' => $this->counter,
            'attributes' => $this->attributes,
            'types' => $this->types,
            'effect' => $this->effect,
            'trigger' => $this->trigger,
            'img_url' => $this->img_url,
        ];
    }
}
