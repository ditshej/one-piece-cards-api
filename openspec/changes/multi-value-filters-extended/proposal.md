## Why

Following the `cost` array-notation extension, the remaining card filters still only accept single values. Consumers building deck simulators or search UIs need OR logic within filter types — e.g. "show me SR or SEC cards from OP13 or OP15 with Blocker or Rush" — which requires multiple values per filter. This change completes the multi-value capability across all applicable filters.

## What Changes

- `color` — accepts `?color[]=Red&color[]=Yellow` (OR: cards that contain at least one of the given colors)
- `rarity` — accepts `?rarity[]=SR&rarity[]=SEC` (OR: whereIn)
- `card_set` — accepts `?card_set[]=OP13&card_set[]=OP15` (OR: whereIn)
- `type` — accepts `?type[]=Minks&type[]=Strawhats` (OR: cards whose types array contains at least one of the given types)
- `keyword` — accepts `?keyword[]=Blocker&keyword[]=Rush` (OR: cards with [Blocker] OR [Rush] in effect or trigger)
- `power` — **new param** `?power[]=8000&power[]=10000` (OR: whereIn; currently only power_min/power_max range exists)

## Capabilities

### New Capabilities

*(none — all extensions to existing capability)*

### Modified Capabilities

- `card-filtering`: multi-value support for color, rarity, card_set, type, keyword; new power exact-match param with multi-value support

## Non-goals

- `category` (4 fixed values, multi-value not useful in practice)
- `attribute` (keep single-value for now)
- `name`, `search` (free-text, OR makes no sense)
- Changing AND logic between different filter types (stays AND-combined)
- Pagination or sorting changes

## Impact

- `GET /api/v1/cards` — 6 filter params extended, 1 new param added
- `app/Http/Requests/CardsIndexRequest.php`
- `app/Models/Card.php` (`scopeApplyFilters`)
- `tests/Feature/Api/CardsEndpointTest.php`
