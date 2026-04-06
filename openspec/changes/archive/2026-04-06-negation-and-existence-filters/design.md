## Context

The `multi-value-filters-extended` change established the normalization pattern and SQL strategies for inclusion filters. This change extends the same two files (`CardsIndexRequest`, `scopeApplyFilters`) with exclusion and existence variants.

Three SQL patterns are needed:

1. **whereNotIn** — scalar columns (`rarity`, `card_set`, `category`, `cost`, `power`, `counter`)
2. **whereJsonDoesntContain loop (AND)** — JSON array columns (`color`, `type`, `attribute`)
3. **where NOT LIKE loop (AND)** — keyword bracket matching (`keyword`)
4. **whereNull / whereNotNull** — existence filters (`has_trigger`, `has_effect`, `has_counter`)

The key semantic difference between positive and negative multi-value:

```
Positive (OR): attribute[]=Slash&attribute[]=Strike  → has Slash OR Strike
Negative (AND): attribute_not[]=Slash&attribute_not[]=Strike → has neither Slash NOR Strike
```

## Goals / Non-Goals

**Goals:**
- Consistent `_not[]` negation for all 9 array-style params
- `has_*` boolean existence filter for `trigger`, `effect`, `counter`
- `counter[]` and `counter_not[]` as new filters (currently missing from both request and scope)
- Scalar-to-array normalization in `scopeApplyFilters` (already in place) covers `_not[]` params too
- Backward-compatible: all existing filters unchanged

**Non-Goals:**
- Range negation (`cost_not_min` etc.)
- Negation for free-text params (`name`, `search`)
- `has_power`, `has_colors` — not needed; power/colors are always present for relevant categories

## Decisions

**Naming convention: `_not[]` suffix**

`attribute_not[]=Slash` over alternatives like `not[attribute][]=Slash` or `attribute[]=!Slash`.

Rationale: mirrors the existing `_min`/`_max` suffix pattern; no new nesting; consistent with how array notation already works for positive filters; easy to document and extend.

**Negation multi-value semantics: AND**

Each `_not[]` value is an additional exclusion. `attribute_not[]=Slash&attribute_not[]=Strike` means "no Slash AND no Strike" — not "no (Slash OR Strike)" (which would be the same thing, but framed as OR it implies a different mental model). AND-framing matches user intuition: "exclude each of these things."

**JSON negation: whereJsonDoesntContain loop (no OR grouping)**

Positive: `WHERE (json_contains(colors, 'Red') OR json_contains(colors, 'Blue'))`
Negative: `WHERE NOT json_contains(colors, 'Slash') AND NOT json_contains(colors, 'Strike')`

No `where()` grouping needed for negation — each condition is independently AND-chained on the main query.

```php
->when($filters['attribute_not'] ?? null, function ($q, $attributesNot) {
    foreach ($attributesNot as $attribute) {
        $q->whereJsonDoesntContain('attributes', $attribute);
    }
})
```

**Keyword negation: AND NOT LIKE on both columns**

`keyword_not[]=Blocker` → effect NOT LIKE '%[Blocker]%' AND trigger NOT LIKE '%[Blocker]%'

Each keyword in the array adds another AND pair, so `keyword_not[]=Blocker&keyword_not[]=Rush` produces four NOT LIKE conditions.

**Existence filters: nullable boolean validation**

`has_trigger`, `has_effect`, `has_counter` accept `true`/`false`/`1`/`0` via Laravel's `boolean` validation rule. Both directions are supported — `has_trigger=false` returns cards with null trigger.

**`counter` normalization: same pattern as other array params**

Added to the normalization loop in `prepareForValidation()` and `scopeApplyFilters`. Integer values (1000, 2000) follow the same `whereIn` pattern as `cost[]` and `power[]`.

**prepareForValidation scope: `_not[]` params also normalized**

`color_not`, `attribute_not`, etc. added to the normalization loop so scalar strings are wrapped into arrays, consistent with positive params.

## Risks / Trade-offs

- `whereJsonDoesntContain` loop — generates one NOT JSON_CONTAINS per value; no performance concern for 2–5 values.
- `has_counter=false` overlaps semantically with `category_not[]=Character` for most cards, but they are distinct: a Character card could theoretically have null counter (edge case in the data). The existence filter is more precise.
- Combining `counter[]` (positive) with `counter_not[]` (negative) on the same request is technically valid but logically contradictory. Not validated — GIGO applies.

## Open Questions

*(none)*
