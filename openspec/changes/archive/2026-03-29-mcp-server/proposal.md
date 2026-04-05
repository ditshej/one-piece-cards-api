## Why

The API now has an OpenAPI spec for humans and HTTP clients — but AI assistants (e.g. Claude in a deck builder project) still have to formulate HTTP calls. An MCP server allows direct data access without URL knowledge, without HTTP overhead, and with semantic tools like "find all red characters with cost 4".

## What Changes

- Install `laravel/mcp` and configure an MCP server in the Laravel app
- Create 4 MCP tools that access the Eloquent models directly:
  - `list-packs` — return all packs
  - `get-pack` — a pack with its cards (by ID)
  - `list-cards` — cards with optional filters (color, category, cost, pack_id, search)
  - `get-card` — a single card (by ID)
- MCP server accessible at `/mcp` (HTTP SSE transport)

## Capabilities

### New Capabilities

- `mcp`: MCP server with tools for direct AI access to packs and cards

### Modified Capabilities

<!-- no existing specs affected -->

## Impact

- New Composer dependency: `laravel/mcp`
- New tool classes under `app/MCP/Tools/`
- MCP route at `/mcp` (SSE)
- No impact on existing API routes under `/api/v1/*`
- No breaking change

## Non-goals

- Authentication of the MCP endpoint (public, like the REST API)
- Write operations (read-only)
- Streaming/subscriptions for card updates
