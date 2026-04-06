## Why

Card filter consumers — deck simulators, search UIs — need to express exclusion and presence alongside the existing inclusion filters. Without negation and existence filters, queries like "all Green cards from OP15 without the Slash attribute" or "all cards that have a trigger but no [Blocker] keyword" require client-side post-processing of large result sets.

## What Changes

- **Negation filters (`_not[]`)** — for all 9 array-style filter params: `color_not[]`, `rarity_not[]`, `card_set_not[]`, `category_not[]`, `type_not[]`, `attribute_not[]`, `keyword_not[]`, `cost_not[]`, `power_not[]`
- **Existence filters (`has_*`)** — boolean params for nullable text/integer columns: `has_trigger`, `has_effect`, `has_counter`
- **`counter` positive filter** — `counter[]` array-notation exact-match filter (currently unfiltered despite being in the data model). Values: `null`, `1000`, `2000`.
- **`counter_not[]`** — negation for `counter`

## Capabilities

### New Capabilities

*(none — all extensions to existing capability)*

### Modified Capabilities

- `card-filtering`: negation variants for all 9 array params, 3 existence filters, counter filter added

## Non-goals

- Range filters for `counter` (`counter_min`/`counter_max`) — only three discrete values exist
- Negation for `name`, `search`, `pack` (free-text / scalar-only params)
- Negation for range params (`cost_min`, `cost_max`, `power_min`, `power_max`) — range inversion is already achievable by adjusting the range bounds
- Combining `has_counter=true` with `category[]=Leader` — these are independent filters; AND-combination works naturally

## Impact

- `GET /api/v1/cards` — 10 new params (`*_not[]` variants) + 3 existence params + 1 new positive filter (`counter[]`)
- `app/Http/Requests/CardsIndexRequest.php`
- `app/Models/Card.php` (`scopeApplyFilters`)
- `tests/Feature/Api/CardsEndpointTest.php`
