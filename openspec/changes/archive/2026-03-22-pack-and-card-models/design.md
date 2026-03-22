## Context

The project has no database layer yet. We need to create the foundational data model for two entities (Pack and Card) that all other features build upon. The data originates from vegapull JSON files with a fixed structure we cannot change.

## Goals / Non-Goals

**Goals:**
- Establish Pack and Card as Eloquent models with correct relationships
- Store vegapull data faithfully without transformation loss
- Enable testability with factories for TDD workflow

**Non-Goals:**
- No query optimization or indexing (premature at this stage)
- No API layer or business logic
- No data seeding with real card data

## Decisions

### 1. String primary keys from vegapull IDs

Use vegapull's own identifiers (`OP01`, `OP01-001`) as primary keys instead of auto-incrementing integers with a separate slug column.

**Why:** The vegapull IDs are already unique, stable, and meaningful. Adding an integer PK would create a redundant layer — every query, every API response, every import would need to map between the two. The IDs are short strings, not UUIDs, so performance impact is negligible.

**Alternative considered:** Integer PK + unique string column. Rejected because it adds complexity with no benefit for this use case.

### 2. JSON columns for array fields (colors, attributes, types)

Store `colors`, `attributes`, and `types` as JSON columns with Eloquent array casts, instead of normalized pivot tables.

**Why:** These arrays are read-heavy, write-rare (only during import), and always accessed as complete lists. Pivot tables would add 3 extra tables and joins for data that is never queried individually. SQLite (current) and PostgreSQL (future) both support JSON columns and `whereJsonContains` for filtering.

**Alternative considered:** Pivot tables (`card_color`, `card_attribute`, `card_type`). Rejected because it over-normalizes data that comes as flat arrays from vegapull and is always consumed as arrays by the API.

### 3. Only `up()` in migrations

Following Spatie convention: no `down()` methods. Rollbacks in production use backup restoration, not reverse migrations. This keeps migrations simple and avoids maintaining fragile rollback logic.

## Risks / Trade-offs

**[JSON column filtering performance]** → Acceptable for now. `whereJsonContains` works on both SQLite and PostgreSQL. If filtering becomes a bottleneck at scale, we can add GIN indexes (PostgreSQL) later without schema changes.

**[String PK join performance]** → Negligible. Pack-Card is the only relationship, and pack IDs are 2-6 characters. No measurable difference vs. integer joins at this data volume (~3000 cards).
