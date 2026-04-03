## Context

Bearer token authentication is already enforced on all `/api/v1/*` endpoints via middleware. The Scramble-generated OpenAPI spec at `/docs/api.json` and the Stoplight Elements UI at `/docs/api` currently have no `securitySchemes` or `security` entries — so the "Authorize" button never appears and every endpoint is shown as public. Developers have no way to discover or test authentication from the docs.

Scramble supports adding security schemes via its `afterOpenApiGenerated` hook (or an `OpenApiVisitor`), which allows post-processing the OpenAPI document to inject `components.securitySchemes` and a global `security` requirement.

## Goals / Non-Goals

**Goals:**
- Add a `BearerToken` HTTP security scheme to the generated OpenAPI 3.1 spec.
- Apply the scheme globally so every endpoint shows the lock icon and participates in the "Authorize" dialog.
- Expose the integration point in `AppServiceProvider::boot()` to keep configuration centralised.

**Non-Goals:**
- Changing authentication enforcement (middleware stays untouched).
- Supporting OAuth2, API key in query/cookie, or any other scheme.
- Adding API key issuance or management features.

## Decisions

### Use Scramble's `afterOpenApiGenerated` hook rather than a custom Extension class

**Decision**: Register a callback via `Scramble::afterOpenApiGenerated()` in `AppServiceProvider::boot()` to mutate the OpenAPI document array directly.

**Rationale**: For a single, simple security scheme injection, a full `OpenApiVisitor`/Extension class is over-engineering. The `afterOpenApiGenerated` hook is Scramble's documented escape hatch for exactly this case and keeps the change self-contained in one place.

**Alternative considered**: A dedicated `ScrambleExtension` class. More structured for complex transformations, but unnecessary overhead for adding two keys to the document.

### Global security requirement (not per-endpoint)

**Decision**: Set security at the top-level `security` field rather than annotating individual routes with `#[SecurityRequirement]` or PHPDoc tags.

**Rationale**: Every `/api/v1/*` endpoint is protected by the same middleware. A global requirement is a single source of truth and avoids per-controller annotation drift. Individual endpoints can override with an empty `security: []` if a public endpoint is added later.

## Risks / Trade-offs

- **Scramble API surface** → The `afterOpenApiGenerated` hook is part of Scramble's public API but less prominent than the config file. If a future Scramble major version removes this hook, the boot code will need updating. Mitigation: the change is isolated to one method call in `AppServiceProvider`.
- **"Try It" still requires CORS** → Stoplight Elements' "Try It" panel will send the Bearer token, but browser CORS restrictions may block cross-origin requests in non-Herd environments. No impact on local dev; acceptable trade-off for now.
- **No runtime validation** → The docs show the auth scheme but do not enforce it themselves — enforcement remains in middleware. This is correct separation of concerns.

## Migration Plan

1. Register `Scramble::afterOpenApiGenerated()` in `AppServiceProvider::boot()`.
2. The callback injects `components.securitySchemes.BearerToken` and `security: [{BearerToken: []}]`.
3. No database changes, no new routes, no middleware changes.
4. Rollback: remove the callback registration — the spec reverts to its current state immediately.
