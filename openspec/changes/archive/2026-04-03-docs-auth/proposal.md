## Why

The API requires Bearer token authentication on all `/api/v1/*` endpoints, but the interactive docs at `/docs/api` give no indication of this — developers hitting the API for the first time have no way to discover the required `Authorization: Bearer <api-key>` header from the documentation itself. This creates unnecessary friction for new consumers.

## What Changes

- Add a Bearer token security scheme to the OpenAPI spec so Scramble-generated docs expose an "Authorize" button and show the authentication requirement on every endpoint.
- Document the authentication header (`Authorization: Bearer <api-key>`) in the API docs UI so developers can understand and test authenticated requests directly from the browser.

## Capabilities

### New Capabilities

- `api-auth-docs`: Adds a `securitySchemes` entry (Bearer / HTTP) to the OpenAPI 3.1 spec and applies it globally so the Scramble-generated docs at `/docs/api` display authentication information and an "Authorize" dialog.

### Modified Capabilities

- `api-docs`: The OpenAPI spec now includes a global `security` requirement and a `securitySchemes` definition — a spec-level change to what the document contains.

## Impact

- **`config/scramble.php`** (or equivalent Scramble configuration): add security scheme definition and global security requirement.
- **`/docs/api`** UI: "Authorize" button becomes visible; every endpoint shows the lock icon indicating it requires authentication.
- **`/docs/api.json`**: JSON output gains `components.securitySchemes` and top-level `security` fields.
- No endpoint behaviour changes — auth enforcement already exists via middleware.
- No breaking changes for API consumers; docs-only addition.

## Non-goals

- Changing how authentication is enforced (middleware is already in place).
- Adding API key management, rotation, or user-facing key generation.
- Supporting authentication schemes other than Bearer token.
