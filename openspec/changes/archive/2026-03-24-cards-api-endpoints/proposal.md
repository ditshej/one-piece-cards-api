## Why

The Packs API is live, but consumers (starting with the Brook OP15 Deck Simulator) need to query individual cards — filtered by color, category, cost, or pack, with text search on effects. This change adds the Cards endpoints that complete the read-only API surface.

## What Changes

- Create `CardsController` with `index` (paginated, filterable, searchable) and `show` actions
- Register card routes in `routes/api.php` under the existing `/v1` prefix
- Filters: `color` (whereJsonContains), `category`, `cost`, `pack` (pack_id)
- Search: `search` parameter for LIKE on `effect` and `trigger` text
- Combined filters supported (e.g. `?color=Red&cost=5`)
- Pagination via Laravel's built-in paginator
- Reuse existing `CardResource` from Change 3

## Non-goals

- Write operations (create, update, delete cards)
- Advanced search (full-text index, fuzzy matching)
- Sorting options beyond default (by id)
- Rate limiting or caching

## Capabilities

### New Capabilities

_(none — all capabilities already have specs)_

### Modified Capabilities

- `api`: Implementing List Cards and Show Card requirements with `/api/v1` prefix, pagination, filters, search
- `cards`: Implementing Card searchability requirement via API query parameters

## Impact

- **New files:** `CardsController`
- **Modified files:** `routes/api.php` (add card routes)
- **APIs:** `GET /api/v1/cards`, `GET /api/v1/cards/{id}`
- **Dependencies:** None — uses built-in Laravel features only
