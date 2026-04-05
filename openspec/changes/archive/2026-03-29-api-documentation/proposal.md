## Why

The API is live at `op-cards.ditshej.ch` but completely undocumented. Other projects (e.g. AI assistants, the Brook Deck Simulator) have to explore the endpoints manually. A machine-readable OpenAPI spec makes the API directly consumable by LLMs without manual explanations.

## What Changes

- Install `dedoc/scramble` (zero-config OpenAPI generator for Laravel)
- Automatically generated interactive documentation at `/docs/api` (Stoplight Elements UI)
- OpenAPI 3.1 JSON spec at `/docs/api.json`
- Publish Scramble config and adapt it to the API (title, version, API prefix `/api/v1`)

## Capabilities

### New Capabilities

- `api-docs`: Automatically generated, interactive API documentation via Scramble — HTML UI for humans, OpenAPI JSON for AI and tooling

### Modified Capabilities

<!-- no existing specs affected -->

## Impact

- New Composer dependency: `dedoc/scramble`
- Two new routes: `GET /docs/api` (UI) and `GET /docs/api.json` (OpenAPI spec)
- No breaking change — additive changes only
- No impact on existing API routes under `/api/v1/*`

## Non-goals

- Writing manual annotations or DocBlocks in controllers
- Documenting auth/rate limiting (not present)
- Versioning of docs artifacts (generated files are not committed)
