## Why

API consumers building deck simulators or search UIs often need to query cards for a specific set of cost values (e.g., "show me all 3-cost and 5-cost cards"), which is impossible with the current single-value `cost` param. Array-notation filtering (`?cost[]=3&cost[]=5`) fills this gap without removing any existing behaviour.

## What Changes

- `CardsIndexRequest` — extend `cost` validation to accept an integer **or** an array of integers; existing `cost_min`/`cost_max` unchanged
- `Card::scopeApplyFilters()` — when `cost` is an array, use `whereIn('cost', ...)` instead of `where('cost', ...)`
- Tests — add scenario for `?cost[]=3&cost[]=5` array filtering

## Capabilities

### New Capabilities

*(none — this extends an existing capability)*

### Modified Capabilities

- `card-filtering`: adds multi-value (array-notation) support to the `cost` filter

## Non-goals

- Adding a `power` exact-match param (only power range exists today; this is a separate decision)
- Changing any other filter params (color, category, pack, etc.)
- Breaking changes — single-value `?cost=5` and range `?cost_min`/`?cost_max` continue to work unchanged

## Impact

- `GET /api/v1/cards` — new query param variant: `?cost[]=3&cost[]=5&cost[]=7`
- `app/Http/Requests/CardsIndexRequest.php`
- `app/Models/Card.php` (`scopeApplyFilters`)
- `tests/Feature/CardsEndpointTest.php`
