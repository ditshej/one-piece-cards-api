## Context

The `cost` filter on `GET /api/v1/cards` currently accepts a single integer (`?cost=5`) via `where('cost', $value)`. Range filtering via `cost_min`/`cost_max` is separate. There is no way to specify a discrete set of values like "3 or 5 or 7".

Current validation in `CardsIndexRequest`: `'cost' => ['nullable', 'integer']`
Current filter in `Card::scopeApplyFilters()`: `->when(...cost..., fn ($q) => $q->where('cost', $filters['cost']))`

## Goals / Non-Goals

**Goals:**
- Accept `?cost[]=3&cost[]=5` (Laravel parses this as an array automatically)
- Preserve single-value `?cost=5` behaviour
- Preserve `cost_min`/`cost_max` range behaviour unchanged

**Non-Goals:**
- Power exact-match param
- Validation of duplicate values in the array

## Decisions

**Validation** — replace `'integer'` with a conditional rule set:

```php
'cost'   => ['nullable', 'array'],
'cost.*' => ['integer'],
```

Laravel's array notation means `?cost=5` arrives as the string `"5"` (not an array), while `?cost[]=5` arrives as `[5]`. To keep single-value support, we accept both via a custom rule or by normalising in the request's `prepareForValidation()`:

```php
protected function prepareForValidation(): void
{
    if ($this->has('cost') && ! is_array($this->cost)) {
        $this->merge(['cost' => [$this->cost]]);
    }
}
```

This normalises both forms to an array before validation — the model always receives an array, keeping `scopeApplyFilters` simple.

**Filter logic** — replace `where()` with `whereIn()`:

```php
->when(isset($filters['cost']), fn ($q) => $q->whereIn('cost', $filters['cost']))
```

No branching needed because normalisation guarantees it's always an array.

**Power** — no changes. When/if a `power` exact-match param is added, the same normalisation pattern applies identically.

## Risks / Trade-offs

- `prepareForValidation` normalisation means the raw request input changes before validation — standard Laravel pattern, no issues expected.
- `whereIn` with a single-element array is functionally identical to `where` — no performance concern.
