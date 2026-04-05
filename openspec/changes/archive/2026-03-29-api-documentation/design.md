## Context

The API has 4 endpoints under `/api/v1/*` and is completely undocumented. `dedoc/scramble` automatically analyzes Laravel routes, controllers and Eloquent Resources and generates an OpenAPI 3.1 spec from them — without annotations.

## Goals / Non-Goals

**Goals:**
- Interactive documentation UI at `/docs/api` (Stoplight Elements)
- Machine-readable OpenAPI spec at `/docs/api.json`
- Zero-annotation approach: no manual maintenance of docblocks

**Non-Goals:**
- Authentication for the docs route
- Versioning of the generated spec (not committed)
- Documentation of non-API routes

## Decisions

### Scramble instead of Scribe

Scribe requires `@response` annotations and is maintenance-intensive. Scramble reads the Eloquent Resources directly and generates the spec fully automatically. For this API (clean resources, no complex auth) the zero-annotation approach is ideal.

### Scramble as production dependency

Scramble is installed as `require` (not `require-dev`) because `/docs/api.json` should be accessible on the production server — for AI projects that consume the spec directly from the live URL.

### No commit of generated docs

Scramble generates the spec at runtime (on-request), not as a static file. There is nothing to commit — the endpoint is always up to date.

## Risks / Trade-offs

- **[Risk] Scramble does not detect all response fields**: If Eloquent Resources use `toArray()` with dynamic fields → Mitigation: Scramble `@response` hints only where necessary
- **[Risk] Docs route public**: `/docs/api` is accessible without auth → not a problem, since the API itself is also public
