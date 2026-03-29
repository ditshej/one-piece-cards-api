## Context

`GET /api/v1/cards` currently has 5 filters: color, category, cost, pack (internal ID), search (effect/trigger LIKE). The MCP `list-cards-tool` mirrors this. Neither supports filtering by rarity, name, attribute, type, numeric ranges, or TCG keywords — all essential for practical TCG queries.

Card data structure (SQLite):
- Scalar fields: `id` (varchar, e.g. OP13-113_p1), `pack_id` (FK), `name`, `rarity`, `category`, `cost` (int), `power` (int), `counter` (int)
- JSON arrays: `colors`, `attributes`, `types`
- Text: `effect`, `trigger`
- TCG keywords always appear as `[Keyword]` in effect/trigger (e.g. `[Blocker]`, `[Rush]`)

## Goals / Non-Goals

**Goals:**
- Filter by every meaningful field (name, rarity, attribute, type, keyword, ranges, alt_art)
- Change `pack` filter to accept label (OP-15) instead of opaque internal ID
- Validate all inputs via Form Request
- MCP tool gets same filters + hard `limit` to prevent oversized responses
- TDD: tests written before implementation

**Non-Goals:**
- Multi-value color filtering
- Boolean AND/OR/NOT search logic
- Sorting / ordering
- Changes to packs endpoint or other tools

## Decisions

### Form Request (`CardsIndexRequest`)
All new parameters go through a dedicated Form Request for clean validation and separation of concerns. Rules:
- `name`: nullable string
- `rarity`: nullable string, in allowed values list
- `attribute`: nullable string, in allowed values
- `type`: nullable string
- `cost_min`, `cost_max`, `power_min`, `power_max`: nullable integer
- `keyword`: nullable string (auto-wrapped in `[` `]` in query)
- `alt_art`: nullable boolean
- `per_page`: nullable integer, between 1–100
- Existing: `color`, `category`, `cost`, `search` — validated too

### Pack filter: label instead of internal ID
Current `?pack=569001` is unusable without documentation. New: `?pack=OP-15` joins `packs` table on `label`. No backwards compatibility for the old format — it's undocumented and the ID format is confusing.

### Keyword filter wraps in brackets
`?keyword=Blocker` → searches `[Blocker]` in effect OR trigger. This correctly identifies cards that *have* the Blocker keyword, not cards that *mention* blockers in their effect text (e.g., "your opponent cannot activate a [Blocker]...").

### Alt art filter
`?alt_art=true` → `WHERE id LIKE '%\_p%'` — matches the `_p1`, `_p2` suffix convention used for alt art cards.

### MCP `list-cards-tool` limit
`limit` parameter (integer, default 20, max 100) replaces unbounded queries. Without this, a single pack can return 2.8M+ characters.

### Reuse query logic
The filter query logic is shared between `CardsController` and `ListCardsTool` — both use Eloquent `when()` chains. No abstraction layer needed (the logic is simple enough to be inline in both places).

## Risks / Trade-offs

- **Breaking change on `pack` filter**: Any existing API consumer using `?pack=<internal_id>` will break. Acceptable since this is a personal project with no known external consumers.
- **MCP default limit of 20**: May surprise users expecting all results. Mitigated by documenting the `limit` parameter clearly in the tool description.
