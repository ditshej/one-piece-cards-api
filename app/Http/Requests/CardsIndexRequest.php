<?php

namespace App\Http\Requests;

use App\Models\Card;
use Illuminate\Foundation\Http\FormRequest;

class CardsIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (Card::ARRAY_FILTER_PARAMS as $param) {
            if ($this->has($param) && ! is_array($this->$param)) {
                $this->merge([$param => [$this->$param]]);
            }
        }

        foreach (['has_trigger', 'has_effect', 'has_counter', 'alt_art'] as $param) {
            if ($this->has($param) && is_string($this->$param)) {
                $this->merge([$param => filter_var($this->$param, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
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
            'color_not' => ['nullable', 'array'],
            'color_not.*' => ['string'],
            'category' => ['nullable', 'array'],
            'category.*' => ['string', 'in:Character,Event,Leader,Stage'],
            'category_not' => ['nullable', 'array'],
            'category_not.*' => ['string', 'in:Character,Event,Leader,Stage'],
            'cost' => ['nullable', 'array'],
            'cost.*' => ['integer'],
            'cost_min' => ['nullable', 'integer'],
            'cost_max' => ['nullable', 'integer'],
            'cost_not' => ['nullable', 'array'],
            'cost_not.*' => ['integer'],
            'power' => ['nullable', 'array'],
            'power.*' => ['integer'],
            'power_min' => ['nullable', 'integer'],
            'power_max' => ['nullable', 'integer'],
            'power_not' => ['nullable', 'array'],
            'power_not.*' => ['integer'],
            'counter' => ['nullable', 'array'],
            'counter.*' => ['integer'],
            'counter_not' => ['nullable', 'array'],
            'counter_not.*' => ['integer'],
            'pack' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'rarity' => ['nullable', 'array'],
            'rarity.*' => ['string'],
            'rarity_not' => ['nullable', 'array'],
            'rarity_not.*' => ['string'],
            'attribute' => ['nullable', 'array'],
            'attribute.*' => ['string'],
            'attribute_not' => ['nullable', 'array'],
            'attribute_not.*' => ['string'],
            'type' => ['nullable', 'array'],
            'type.*' => ['string'],
            'type_not' => ['nullable', 'array'],
            'type_not.*' => ['string'],
            'keyword' => ['nullable', 'array'],
            'keyword.*' => ['string'],
            'keyword_not' => ['nullable', 'array'],
            'keyword_not.*' => ['string'],
            'card_set' => ['nullable', 'array'],
            'card_set.*' => ['string'],
            'card_set_not' => ['nullable', 'array'],
            'card_set_not.*' => ['string'],
            'has_trigger' => ['nullable', 'boolean'],
            'has_effect' => ['nullable', 'boolean'],
            'has_counter' => ['nullable', 'boolean'],
            'alt_art' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
