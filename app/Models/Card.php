<?php

namespace App\Models;

use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'pack_id', 'name', 'rarity', 'category', 'colors', 'cost', 'power', 'counter', 'attributes', 'types', 'effect', 'trigger', 'img_url'])]
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
        ];
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    /**
     * Apply all supported filter parameters from an associative array.
     *
     * Supported keys: color, category, cost, cost_min, cost_max, power_min, power_max,
     * pack_label, search, name, rarity, attribute, type, keyword, alt_art.
     *
     * @param  Builder<Card>  $query
     * @param  array<string, mixed>  $filters
     */
    public function scopeApplyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['color'] ?? null, fn ($q, $color) => $q->whereJsonContains('colors', $color))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
            ->when($filters['cost'] ?? null, fn ($q, $cost) => $q->where('cost', $cost))
            ->when($filters['cost_min'] ?? null, fn ($q, $min) => $q->where('cost', '>=', $min))
            ->when($filters['cost_max'] ?? null, fn ($q, $max) => $q->where('cost', '<=', $max))
            ->when($filters['power_min'] ?? null, fn ($q, $min) => $q->where('power', '>=', $min))
            ->when($filters['power_max'] ?? null, fn ($q, $max) => $q->where('power', '<=', $max))
            ->when($filters['pack_label'] ?? null, fn ($q, $label) => $q->whereHas('pack', fn ($r) => $r->where('label', $label)))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%{$search}%")
                    ->orWhere('trigger', 'LIKE', "%{$search}%")
            ))
            ->when($filters['name'] ?? null, fn ($q, $name) => $q->where('name', 'LIKE', "%{$name}%"))
            ->when($filters['rarity'] ?? null, fn ($q, $rarity) => $q->where('rarity', $rarity))
            ->when($filters['attribute'] ?? null, fn ($q, $attribute) => $q->whereJsonContains('attributes', $attribute))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->whereJsonContains('types', $type))
            ->when($filters['keyword'] ?? null, fn ($q, $keyword) => $q->where(
                fn ($sub) => $sub->where('effect', 'LIKE', "%[{$keyword}]%")
                    ->orWhere('trigger', 'LIKE', "%[{$keyword}]%")
            ))
            ->when($filters['alt_art'] ?? false, fn ($q) => $q->whereRaw("INSTR(id, '_p') > 0"));
    }
}
