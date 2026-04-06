<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'pack_id', 'card_set', 'name', 'rarity', 'category', 'colors', 'cost', 'power', 'counter', 'attributes', 'types', 'effect', 'trigger', 'img_url', 'alt_art_variant'])]
class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'colors' => 'array',
            'attributes' => 'array',
            'types' => 'array',
            'alt_art_variant' => 'integer',
        ];
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    /**
     * Apply all supported filter parameters from an associative array.
     *
     * Supported keys: color, category, cost, cost_min, cost_max, power, power_min, power_max,
     * pack_label, search, name, rarity, attribute, type, keyword, card_set, alt_art.
     *
     * @param  Builder<Card>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeApplyFilters(Builder $query, array $filters): void
    {
        foreach (['cost', 'power', 'color', 'rarity', 'card_set', 'category', 'type', 'attribute', 'keyword'] as $param) {
            if (isset($filters[$param]) && ! is_array($filters[$param])) {
                $filters[$param] = [$filters[$param]];
            }
        }

        $query
            ->when($filters['color'] ?? null, function ($q, $colors) {
                $q->where(function ($sub) use ($colors) {
                    foreach ($colors as $color) {
                        $sub->orWhereJsonContains('colors', $color);
                    }
                });
            })
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->whereIn('category', $category))
            ->when($filters['cost'] ?? null, fn ($q, $cost) => $q->whereIn('cost', $cost))
            ->when(isset($filters['cost_min']), fn ($q) => $q->where('cost', '>=', $filters['cost_min']))
            ->when(isset($filters['cost_max']), fn ($q) => $q->where('cost', '<=', $filters['cost_max']))
            ->when($filters['power'] ?? null, fn ($q, $power) => $q->whereIn('power', $power))
            ->when(isset($filters['power_min']), fn ($q) => $q->where('power', '>=', $filters['power_min']))
            ->when(isset($filters['power_max']), fn ($q) => $q->where('power', '<=', $filters['power_max']))
            ->when($filters['pack_label'] ?? null, fn ($q, $label) => $q->whereHas('pack', fn ($r) => $r->where('label', $label)))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%{$search}%")
                    ->orWhere('trigger', 'LIKE', "%{$search}%")
            ))
            ->when($filters['name'] ?? null, fn ($q, $name) => $q->where('name', 'LIKE', "%{$name}%"))
            ->when($filters['rarity'] ?? null, fn ($q, $rarity) => $q->whereIn('rarity', $rarity))
            ->when($filters['attribute'] ?? null, function ($q, $attributes) {
                $q->where(function ($sub) use ($attributes) {
                    foreach ($attributes as $attribute) {
                        $sub->orWhereJsonContains('attributes', $attribute);
                    }
                });
            })
            ->when($filters['type'] ?? null, function ($q, $types) {
                $q->where(function ($sub) use ($types) {
                    foreach ($types as $type) {
                        $sub->orWhereJsonContains('types', $type);
                    }
                });
            })
            ->when($filters['keyword'] ?? null, function ($q, $keywords) {
                $q->where(function ($sub) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $sub->orWhere('effect', 'LIKE', "%[{$keyword}]%")
                            ->orWhere('trigger', 'LIKE', "%[{$keyword}]%");
                    }
                });
            })
            ->when($filters['card_set'] ?? null, fn ($q, $cardSet) => $q->whereIn('card_set', $cardSet))
            ->when($filters['alt_art'] ?? false, fn ($q) => $q->whereNotNull('alt_art_variant'));
    }
}
