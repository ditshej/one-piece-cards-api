## Context

The `cost` change established the normalization pattern: `prepareForValidation()` wraps a scalar into a single-element array, so the model always receives an array regardless of whether the client sends `?cost=5` or `?cost[]=5`. The same pattern applies to all new filters.

Three distinct SQL patterns are needed depending on the column type:

1. **whereIn** — for scalar columns (`rarity`, `card_set`, `power`)
2. **orWhereJsonContains loop** — for JSON array columns (`color`, `type`)
3. **orWhere LIKE loop** — for keyword bracket matching (`keyword`)

## Goals / Non-Goals

**Goals:**
- Consistent scalar→array normalization for all 6 filters
- OR logic within each filter via the appropriate SQL pattern
- Backward-compatible: single values continue to work

**Non-Goals:**
- `category`, `attribute`, free-text filters
- AND logic within a single filter type

## Decisions

**Normalization (prepareForValidation)** — extend existing method to handle all new array params:

```php
protected function prepareForValidation(): void
{
    foreach (['cost', 'power', 'rarity', 'card_set', 'color', 'type', 'keyword'] as $param) {
        if ($this->has($param) && ! is_array($this->$param)) {
            $this->merge([$param => [$this->$param]]);
        }
    }
}
```

**Validation rules:**
```php
'color'    => ['nullable', 'array'],
'color.*'  => ['string'],
'rarity'   => ['nullable', 'array'],
'rarity.*' => ['string'],
'card_set'    => ['nullable', 'array'],
'card_set.*'  => ['string'],
'type'     => ['nullable', 'array'],
'type.*'   => ['string'],
'keyword'  => ['nullable', 'array'],
'keyword.*'=> ['string'],
'power'    => ['nullable', 'array'],
'power.*'  => ['integer'],
```

**scopeApplyFilters patterns:**

```php
// whereIn (rarity, card_set, power)
->when($filters['rarity'] ?? null, fn ($q, $rarity) => $q->whereIn('rarity', $rarity))
->when($filters['card_set'] ?? null, fn ($q, $cardSet) => $q->whereIn('card_set', $cardSet))
->when($filters['power'] ?? null, fn ($q, $power) => $q->whereIn('power', $power))

// JSON containment OR (color, type)
->when($filters['color'] ?? null, fn ($q, $colors) =>
    $q->where(fn ($sub) => collect($colors)
        ->each(fn ($c) => $sub->orWhereJsonContains('colors', $c)))
)
->when($filters['type'] ?? null, fn ($q, $types) =>
    $q->where(fn ($sub) => collect($types)
        ->each(fn ($t) => $sub->orWhereJsonContains('types', $t)))
)

// Keyword OR across effect + trigger
->when($filters['keyword'] ?? null, fn ($q, $keywords) =>
    $q->where(fn ($sub) => collect($keywords)
        ->each(fn ($kw) => $sub->orWhere('effect', 'LIKE', "%[{$kw}]%")
                               ->orWhere('trigger', 'LIKE', "%[{$kw}]%")))
)
```

**power param + power_min/power_max coexistence** — `power` (exact-match array) and `power_min`/`power_max` (range) are independent filters, both applied with AND. A client can combine them if needed (unusual but valid).

## Risks / Trade-offs

- `orWhereJsonContains` loop — SQLite supports JSON functions; tested already in existing color filter. OR loop generates multiple `OR json_contains()` clauses, acceptable for typical filter arrays (2–5 values).
- `keyword` OR loop — generates `OR effect LIKE ... OR trigger LIKE ...` per keyword. Fine for small arrays; not optimized for 10+ keywords, but that's not a realistic use case.

## Open Questions

*(none)*
