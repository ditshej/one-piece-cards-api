## Why

The Pack and Card models exist, and the import command populates them — but there's no way to consume this data externally. The first consumer (Brook OP15 Deck Simulator) needs HTTP endpoints to query packs and their cards. This change establishes the API infrastructure (`/api/v1` routing, Eloquent Resources) and delivers the Packs endpoints.

## What Changes

- Create `routes/api.php` with `/v1` prefix group, registered in `bootstrap/app.php`
- Create `PackResource` and `CardResource` Eloquent API Resources
- Create `PacksController` with `index` and `show` actions
- Pack `show` includes nested cards via `CardResource`
- Proper 404 JSON responses for missing packs

## Non-goals

- Card-specific endpoints (filtering, search, pagination) — that's Change 4 (`cards-api-endpoints`)
- Authentication or rate limiting
- API documentation (Swagger/OpenAPI)
- Pagination on pack index (small dataset, all packs returned at once)

## Capabilities

### New Capabilities

_(none — all capabilities already have specs)_

### Modified Capabilities

- `api`: Implementing the List Packs, Show Pack, JSON API Resources, and API Versioning requirements
- `packs`: Implementing the Pack listing requirement via the API

## Impact

- **New files:** `routes/api.php`, `PacksController`, `PackResource`, `CardResource`
- **Modified files:** `bootstrap/app.php` (API route registration)
- **APIs:** `GET /api/v1/packs`, `GET /api/v1/packs/{id}`
- **Dependencies:** None — uses built-in Laravel features only
