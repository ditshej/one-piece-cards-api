## Context

The API infrastructure (`/api/v1` routing, CardResource, PackResource) is in place from Change 3. The Card model already has JSON-cast array fields (`colors`, `attributes`, `types`). This change adds the card-specific endpoints on top of that foundation.

## Goals / Non-Goals

**Goals:**
- Deliver paginated, filterable, searchable card index endpoint
- Deliver card show endpoint with full card details
- Reuse existing `CardResource` — no new resources needed

**Non-Goals:**
- Write operations (this is a read-only API)
- Full-text search indexes (LIKE is sufficient for current dataset size)
- Custom pagination page sizes (use Laravel default of 15)

## Decisions

### 1. Filter implementation via query scopes or inline `when()`

Use inline `when()` chaining on the query builder inside the controller. No model scopes.

**Why:** With only 4 filters + 1 search, dedicated scopes would be over-engineering. The `when()` pattern keeps filtering logic visible in one place. If filters grow significantly, scopes can be extracted later.

### 2. Color filter uses `whereJsonContains`

`Card::query()->whereJsonContains('colors', $color)` to match cards containing a specific color in their JSON array.

**Why:** SQLite and PostgreSQL both support `whereJsonContains`. Matches the data model (colors is a JSON array). Single-color filter per request is sufficient for the deck simulator use case.

### 3. Search across effect and trigger fields

`where(fn ($q) => $q->where('effect', 'LIKE', "%{$search}%")->orWhere('trigger', 'LIKE', "%{$search}%"))` to search both text fields.

**Why:** Covers the most common search use case (finding cards with specific abilities). Grouped in a closure to not break combined filters with OR logic.

### 4. Laravel built-in pagination

Use `->paginate()` which returns a `LengthAwarePaginator` wrapped by `CardResource::collection()`. Returns standard `data`, `links`, `meta` structure.

**Why:** Laravel's paginator is the convention. Returns page metadata that consumers need. Default page size of 15 is reasonable for card browsing.

### 5. Card show does not include pack relationship

Unlike pack show (which includes cards), card show returns only the card fields via `CardResource`. The `pack_id` field is already present in the response.

**Why:** The pack relationship would add one field (pack name) at the cost of an extra query. Consumers can look up the pack if needed. Keeps it simple.

## Risks / Trade-offs

- **[LIKE performance on large datasets]** → Current OPTCG has ~2000 cards. LIKE with leading wildcard doesn't use indexes but is fast at this scale. Revisit if dataset grows 10x+.
- **[No input validation on filter values]** → Invalid filter values simply return empty results, which is acceptable REST behavior. A Form Request would add complexity for no benefit on a read-only endpoint.
