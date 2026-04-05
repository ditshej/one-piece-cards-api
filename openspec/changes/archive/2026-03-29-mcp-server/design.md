## Context

`laravel/mcp` is already installed via `laravel/boost` (v0.6.4). The Artisan commands `make:mcp-server` and `make:mcp-tool` are available. The existing Eloquent models (`Pack`, `Card`) and the filter logic from `CardsController` can be reused directly.

## Goals / Non-Goals

**Goals:**
- MCP server with 4 tools: `list-packs`, `get-pack`, `list-cards`, `get-card`
- Direct Eloquent access (no HTTP overhead)
- Tools return the same data as the REST API

**Non-Goals:**
- Separate `laravel/mcp` installation (already via boost)
- Auth/middleware for the MCP endpoint
- Write tools

## Decisions

### Direct Eloquent queries instead of HTTP calls

The MCP tools access the models directly — no internal HTTP call to the REST API. This is more efficient and avoids circularity.

### Reuse filter logic from CardsController

`list-cards` implements the same `->when()` filter chain as `CardsController@index`. No separate service needed — the logic is simple enough for direct code.

### Tool structure

One MCP server (`CardsServer`) with 4 registered tools. Each tool in its own file under `app/MCP/Tools/`. Register server via `McpServiceProvider` or directly in `AppServiceProvider`.

### Return format

Tools return arrays (no JSON string). `laravel/mcp` serializes automatically. Fields identical to the Eloquent Resources of the REST API.

## Risks / Trade-offs

- **[Risk] laravel/mcp API changes**: Package is v0.x — breaking changes possible → Mitigation: pin version in composer.json
- **[Risk] N+1 with `list-packs` and cards**: `get-pack` loads cards eagerly, `list-packs` without → not a problem, since `list-packs` does not return cards
