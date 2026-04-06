<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CardsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['cost', 'power', 'color', 'rarity', 'card_set', 'category', 'type', 'attribute', 'keyword'] as $param) {
            if ($this->has($param) && ! is_array($this->$param)) {
                $this->merge([$param => [$this->$param]]);
            }
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'color' => ['nullable', 'array'],
            'color.*' => ['string'],
            'category' => ['nullable', 'array'],
            'category.*' => ['string', 'in:Character,Event,Leader,Stage'],
            'cost' => ['nullable', 'array'],
            'cost.*' => ['integer'],
            'cost_min' => ['nullable', 'integer'],
            'cost_max' => ['nullable', 'integer'],
            'power' => ['nullable', 'array'],
            'power.*' => ['integer'],
            'power_min' => ['nullable', 'integer'],
            'power_max' => ['nullable', 'integer'],
            'pack' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'rarity' => ['nullable', 'array'],
            'rarity.*' => ['string'],
            'attribute' => ['nullable', 'array'],
            'attribute.*' => ['string'],
            'type' => ['nullable', 'array'],
            'type.*' => ['string'],
            'keyword' => ['nullable', 'array'],
            'keyword.*' => ['string'],
            'card_set' => ['nullable', 'array'],
            'card_set.*' => ['string'],
            'alt_art' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
