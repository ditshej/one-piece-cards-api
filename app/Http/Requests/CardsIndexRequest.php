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
        if ($this->has('cost') && ! is_array($this->cost)) {
            $this->merge(['cost' => [$this->cost]]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'color' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'in:Character,Event,Leader,Stage'],
            'cost' => ['nullable', 'array'],
            'cost.*' => ['integer'],
            'cost_min' => ['nullable', 'integer'],
            'cost_max' => ['nullable', 'integer'],
            'power_min' => ['nullable', 'integer'],
            'power_max' => ['nullable', 'integer'],
            'pack' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'rarity' => ['nullable', 'string'],
            'attribute' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'keyword' => ['nullable', 'string'],
            'card_set' => ['nullable', 'string'],
            'alt_art' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'between:1,100'],
        ];
    }
}
