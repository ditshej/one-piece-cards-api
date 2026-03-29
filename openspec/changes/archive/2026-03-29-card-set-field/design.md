## Context

Cards have string IDs in the format `{SetPrefix}-{Number}` (e.g., `OP03-072`) or with alt art suffix `{SetPrefix}-{Number}_p{Variant}` (e.g., `OP03-072_p1`). The origin set prefix is currently only accessible by string manipulation at query time, and alt art detection uses `INSTR(id, '_p') > 0` which is not indexable.

Two derived fields will be persisted at import time: `card_set` (the set prefix) and `alt_art_variant` (the variant number or null).

## Goals / Non-Goals

**Goals:**
- Store `card_set` and `alt_art_variant` as first-class columns on `cards`
- Enable `?card_set=OP03` filtering via indexed column
- Improve `alt_art` filter from raw string function to column comparison
- Expose both fields in the API response

**Non-Goals:**
- Filtering by specific variant number (`?alt_art_variant=2`)
- Changing the pack/set relationship structure
- Supporting multi-set cards or cards with no set prefix

## Decisions

### Derive at write time, not query time
Computing `card_set` from `substr(id, ...)` in every SQL query adds per-row overhead and prevents indexing. Storing it as a column at import time (and via migration backfill) is consistent with how `pack_id` works.

*Alternatives considered:* Generated/computed column (not supported uniformly across SQLite + future PostgreSQL); virtual column (not indexable in SQLite without additional configuration).

### `alt_art_variant` as INTEGER over BOOLEAN
Multiple alt art variants per card (`_p1`, `_p2`) make a boolean lossy. An integer preserves the variant index and is backward-compatible with boolean-style queries (`IS NOT NULL` replaces `= true`).

*Alternative considered:* Separate `is_alt_art` boolean — discarded because it loses variant information.

### Extraction via `preg_match` in PHP (import) and SQL `substr`/`instr` (migration backfill)
The import path uses `preg_match('/_p(\d+)$/', $id, $m)` which is explicit and handles edge cases. The backfill SQL uses SQLite-compatible `CAST(substr(id, instr(id, '_p') + 2) AS INTEGER)` which does the same without a PHP loop.

## Risks / Trade-offs

- [Risk] Cards with unusual ID formats (no `-` separator) would yield incorrect `card_set` → Mitigation: all real OPTCG IDs follow the `{PREFIX}-{NUMBER}` format; validated by inspection of vegapull data.
- [Risk] `alt_art_variant` cast in the model is `integer`, but SQLite stores `null` as `null` — no issue, Laravel nullable integer cast handles this correctly.

## Migration Plan

1. Migration adds `card_set` and `alt_art_variant` columns (nullable)
2. Backfill SQL runs within the same migration `up()` — no separate data migration step needed
3. All new imports automatically populate both fields via `ImportCardsCommand`
4. No rollback needed (only `up()` per project convention)
