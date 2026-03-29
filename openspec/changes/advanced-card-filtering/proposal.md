## Why

The cards endpoint currently supports only 5 basic filters (color, category, cost, pack, search), which is insufficient for real TCG use cases like "all Red Uncommon cards from OP-15" or "all Blocker characters with Cost 6 or higher". The MCP tool also returns entire pack datasets (2.8M+ characters) without any limit, making it unusable for large packs.

## What Changes

- Add `name` filter: case-insensitive LIKE search on card name
- Add `rarity` filter: exact match (Common, Uncommon, Rare, SuperRare, SecretRare, TreasureRare, Special, Promo, Leader, P)
- Add `attribute` filter: JSON array contains (Strike, Slash, Ranged, Wisdom, Special, Unknown)
- Add `type` filter: JSON array contains (e.g., Egghead, Straw Hat Crew)
- Add `cost_min` / `cost_max` filters: numeric range on cost field
- Add `power_min` / `power_max` filters: numeric range on power field
- Add `keyword` filter: searches for `[Keyword]` in effect/trigger text (e.g., `keyword=Blocker` → `[Blocker]`) — more precise than free-text search
- Add `alt_art` filter: boolean, returns only cards with `_p` suffix in ID
- **BREAKING**: Change `?pack=` filter to accept pack label (e.g., OP-15) instead of internal pack_id — more intuitive for API consumers
- Add `per_page` parameter to control pagination size (default 20, max 100)
- MCP `list-cards-tool`: add same filters + `limit` parameter (default 20, max 100)
- Validate all new parameters via a dedicated Form Request

## Capabilities

### New Capabilities

- `card-filtering`: Comprehensive filtering of the cards endpoint with all new parameters, validation, and keyword-aware search

### Modified Capabilities

- `cards`: The `GET /api/v1/cards` endpoint gains new query parameters and changes the `pack` filter to accept labels instead of internal IDs

## Impact

- `app/Http/Controllers/CardsController.php` — extended filter logic
- `app/Http/Requests/CardsIndexRequest.php` — new Form Request (validation)
- `app/Mcp/Tools/ListCardsTool.php` — extended filters + limit
- `tests/Feature/Api/CardsEndpointTest.php` — new test cases
- API consumers using `?pack=<internal_id>` will need to switch to `?pack=OP-15` style

## Non-goals

- Multi-value color filter (e.g., `?colors[]=Red&colors[]=Blue`) — existing single `?color=` filter is sufficient for now
- Full-text search with AND/OR/NOT boolean logic — `keyword` + `search` covers the main use cases
- Sorting / ordering of results
- Changes to the packs endpoint
