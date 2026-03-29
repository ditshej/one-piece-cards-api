## 1. Database Migration

- [x] 1.1 Create migration adding `card_set` (VARCHAR nullable) and `alt_art_variant` (INTEGER nullable) to `cards`
- [x] 1.2 Add backfill SQL in migration `up()` to populate both fields for existing rows

## 2. Model & Factory

- [x] 2.1 Add `card_set` and `alt_art_variant` to `#[Fillable]` on Card model
- [x] 2.2 Add `alt_art_variant` integer cast in Card model `casts()`
- [x] 2.3 Update `CardFactory` to derive `card_set` and `alt_art_variant` from `id`

## 3. Import Command

- [x] 3.1 Compute `card_set` via `explode('-', $id)[0]` in `ImportCardsCommand::cardAttributes()`
- [x] 3.2 Compute `alt_art_variant` via `preg_match('/_p(\d+)$/', $id)` in `ImportCardsCommand::cardAttributes()`

## 4. API

- [x] 4.1 Add `card_set` and `alt_art_variant` to `CardResource::toArray()`
- [x] 4.2 Add `card_set` validation rule to `CardsIndexRequest`
- [x] 4.3 Add `card_set` filter (exact match) to `Card::scopeApplyFilters()`
- [x] 4.4 Update `alt_art` filter in `scopeApplyFilters()` to use `whereNotNull('alt_art_variant')`

## 5. MCP

- [x] 5.1 Add `card_set` parameter to `ListCardsTool::schema()`

## 6. Tests

- [x] 6.1 Write test: `filters cards by card_set`
- [x] 6.2 Write test: `returns card_set and alt_art_variant in response`
- [x] 6.3 Update existing `filters alt art cards only` test to confirm behaviour with new column
- [x] 6.4 Update `returns paginated cards on index` structure assertion to include new fields
- [x] 6.5 Run full test suite and confirm all pass
