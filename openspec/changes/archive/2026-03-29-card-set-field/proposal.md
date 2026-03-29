## Why

Cards have two distinct set concepts: the origin set (derived from the card ID prefix, e.g. `OP03` from `OP03-072`) and the release pack (the physical product). Currently the origin set is only implicit in the ID string, making it impossible to filter "all OP14 cards regardless of which pack they appear in" — a key requirement for deck builders where reprints must be found across packs.

Additionally, alt art variants (`_p1`, `_p2`, etc.) are only detectable via string inspection. Exposing the variant number as an indexed integer field enables cleaner filtering and richer API responses.

## What Changes

- Add `card_set` column (VARCHAR) to `cards` — stores the origin set prefix (e.g. `OP03`, `PRB02`)
- Add `alt_art_variant` column (INTEGER nullable) — stores the variant number from `_p{n}` suffix; `null` for non-alt-art cards
- Both fields computed at import time and backfilled via migration
- `GET /api/v1/cards?card_set=OP03` filter added
- `alt_art` filter migrated from `INSTR(id, '_p') > 0` to `alt_art_variant IS NOT NULL`
- Both fields exposed in `CardResource` response

## Capabilities

### New Capabilities

- `card-set-field`: Stored `card_set` and `alt_art_variant` fields on cards, enabling set-based filtering and alt art variant lookup

### Modified Capabilities

- `cards`: New response fields `card_set` and `alt_art_variant`; new `?card_set=` query parameter; `alt_art` filter behaviour unchanged but now backed by a column
- `card-filtering`: `card_set` filter added; `alt_art` filter implementation changed to use column

## Impact

- `cards` table: 2 new columns
- `ImportCardsCommand`: computes both fields at import
- `CardResource`: 2 new response fields
- `CardsIndexRequest`: new `card_set` validation rule
- `Card::scopeApplyFilters`: new `card_set` clause; updated `alt_art` clause
- `ListCardsTool`: new `card_set` schema parameter
- `CardFactory`: derives both fields from `id`
- Existing `?alt_art=1` behaviour preserved, no breaking changes

## Non-goals

- Filtering by alt art variant number (e.g. `?alt_art_variant=2`) — not needed yet
- Renaming or restructuring the pack system
