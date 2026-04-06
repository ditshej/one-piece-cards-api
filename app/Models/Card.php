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

    /**
     * Filter parameters that accept either a scalar value or an array of values.
     *
     * @var list<string>
     */
    public const ARRAY_FILTER_PARAMS = [
        'cost', 'power', 'color', 'rarity', 'card_set', 'category',
        'type', 'attribute', 'keyword', 'counter',
        'color_not', 'rarity_not', 'card_set_not', 'category_not',
        'type_not', 'attribute_not', 'keyword_not', 'cost_not', 'power_not', 'counter_not',
    ];

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
     * Supported keys: color, color_not, category, category_not, cost, cost_min, cost_max, cost_not,
     * power, power_min, power_max, power_not, counter, counter_not, pack_label, search, name,
     * rarity, rarity_not, attribute, attribute_not, type, type_not, keyword, keyword_not,
     * card_set, card_set_not, has_trigger, has_effect, has_counter, alt_art.
     *
     * @param  Builder<Card>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeApplyFilters(Builder $query, array $filters): void
    {
        foreach (self::ARRAY_FILTER_PARAMS as $param) {
            if (isset($filters[$param]) && ! is_array($filters[$param])) {
                $filters[$param] = [$filters[$param]];
            }
        }

        $query
            ->when($filters['color'] ?? null, fn ($q, $colors) => $this->whereJsonContainsAny($q, 'colors', $colors))
            ->when($filters['color_not'] ?? null, fn ($q, $colorsNot) => $this->whereJsonDoesntContainAll($q, 'colors', $colorsNot))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->whereIn('category', $category))
            ->when($filters['category_not'] ?? null, fn ($q, $categoryNot) => $q->whereNotIn('category', $categoryNot))
            ->when($filters['cost'] ?? null, fn ($q, $cost) => $q->whereIn('cost', $cost))
            ->when(isset($filters['cost_min']), fn ($q) => $q->where('cost', '>=', $filters['cost_min']))
            ->when(isset($filters['cost_max']), fn ($q) => $q->where('cost', '<=', $filters['cost_max']))
            ->when($filters['cost_not'] ?? null, fn ($q, $costNot) => $q->whereNotIn('cost', $costNot))
            ->when($filters['power'] ?? null, fn ($q, $power) => $q->whereIn('power', $power))
            ->when(isset($filters['power_min']), fn ($q) => $q->where('power', '>=', $filters['power_min']))
            ->when(isset($filters['power_max']), fn ($q) => $q->where('power', '<=', $filters['power_max']))
            ->when($filters['power_not'] ?? null, fn ($q, $powerNot) => $q->whereNotIn('power', $powerNot))
            ->when($filters['counter'] ?? null, fn ($q, $counter) => $q->whereIn('counter', $counter))
            ->when($filters['counter_not'] ?? null, function ($q, $counterNot) {
                $q->where(function ($sub) use ($counterNot) {
                    $sub->whereNotIn('counter', $counterNot)->orWhereNull('counter');
                });
            })
            ->when($filters['pack_label'] ?? null, fn ($q, $label) => $q->whereHas('pack', fn ($r) => $r->where('label', $label)))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%{$search}%")
                    ->orWhere('trigger', 'LIKE', "%{$search}%")
            ))
            ->when($filters['name'] ?? null, fn ($q, $name) => $q->where('name', 'LIKE', "%{$name}%"))
            ->when($filters['rarity'] ?? null, fn ($q, $rarity) => $q->whereIn('rarity', $rarity))
            ->when($filters['rarity_not'] ?? null, fn ($q, $rarityNot) => $q->whereNotIn('rarity', $rarityNot))
            ->when($filters['attribute'] ?? null, fn ($q, $attributes) => $this->whereJsonContainsAny($q, 'attributes', $attributes))
            ->when($filters['attribute_not'] ?? null, fn ($q, $attributesNot) => $this->whereJsonDoesntContainAll($q, 'attributes', $attributesNot))
            ->when($filters['type'] ?? null, fn ($q, $types) => $this->whereJsonContainsAny($q, 'types', $types))
            ->when($filters['type_not'] ?? null, fn ($q, $typesNot) => $this->whereJsonDoesntContainAll($q, 'types', $typesNot))
            ->when($filters['keyword'] ?? null, function ($q, $keywords) {
                $q->where(function ($sub) use ($keywords) {
                    foreach ($keywords as $keyword) {
                        $sub->orWhere('effect', 'LIKE', "%[{$keyword}]%")
                            ->orWhere('trigger', 'LIKE', "%[{$keyword}]%");
                    }
                });
            })
            ->when($filters['keyword_not'] ?? null, function ($q, $keywordsNot) {
                foreach ($keywordsNot as $keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->whereNull('effect')->orWhere('effect', 'NOT LIKE', "%[{$keyword}]%");
                    })->where(function ($sub) use ($keyword) {
                        $sub->whereNull('trigger')->orWhere('trigger', 'NOT LIKE', "%[{$keyword}]%");
                    });
                }
            })
            ->when($filters['card_set'] ?? null, fn ($q, $cardSet) => $q->whereIn('card_set', $cardSet))
            ->when($filters['card_set_not'] ?? null, fn ($q, $cardSetNot) => $q->whereNotIn('card_set', $cardSetNot))
            ->when(isset($filters['has_trigger']), function ($q) use ($filters) {
                $filters['has_trigger'] ? $q->whereNotNull('trigger') : $q->whereNull('trigger');
            })
            ->when(isset($filters['has_effect']), function ($q) use ($filters) {
                $filters['has_effect'] ? $q->whereNotNull('effect') : $q->whereNull('effect');
            })
            ->when(isset($filters['has_counter']), function ($q) use ($filters) {
                $filters['has_counter'] ? $q->whereNotNull('counter') : $q->whereNull('counter');
            })
            ->when($filters['alt_art'] ?? false, fn ($q) => $q->whereNotNull('alt_art_variant'));
    }

    /**
     * Constrain the query to rows where the JSON column contains any of the given values (OR logic).
     *
     * @param  Builder<Card>  $query
     * @param  array<int, mixed>  $values
     */
    private function whereJsonContainsAny(Builder $query, string $column, array $values): void
    {
        $query->where(function ($sub) use ($column, $values) {
            foreach ($values as $value) {
                $sub->orWhereJsonContains($column, $value);
            }
        });
    }

    /**
     * Constrain the query to rows where the JSON column does not contain any of the given values (AND NOT logic).
     *
     * @param  Builder<Card>  $query
     * @param  array<int, mixed>  $values
     */
    private function whereJsonDoesntContainAll(Builder $query, string $column, array $values): void
    {
        foreach ($values as $value) {
            $query->whereJsonDoesntContain($column, $value);
        }
    }
}
