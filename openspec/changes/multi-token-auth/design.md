## Context

The API currently authenticates via `ApiKeyMiddleware`, comparing a Bearer token against `config('auth.api_key')` (from `.env`). Multiple apps sharing a single token cannot be individually revoked. The SDK `ditshej/op-cards-php` will configure tokens via `OP_CARDS_API_KEY` — the client interface stays identical, only the server-side changes.

## Goals / Non-Goals

**Goals:**
- Laravel Sanctum Personal Access Tokens bound to `User` model records (one user per consuming app)
- `token:create {name} {email}` Artisan command: create user + issue token, display plaintext once
- `auth:sanctum` guard (or equivalent middleware) protects `api/v1/*` and `POST /mcp`
- `last_used_at` updated automatically by Sanctum on each valid request

**Non-Goals:**
- No login flow, no passwords, no registration endpoint
- No token expiry, no rate limiting, no admin UI

## Decisions

### Laravel Sanctum over custom middleware

**Decision**: Use Laravel Sanctum Personal Access Tokens instead of a custom middleware.

**Rationale**: Sanctum is the standard Laravel approach for API token authentication. It provides token hashing (SHA-256), `last_used_at` tracking, named tokens, and revocation out of the box — without reinventing the wheel. It's a first-party package with full Laravel integration.

**Alternative considered**: Custom `api_tokens` table + custom middleware. Rejected in favor of Sanctum to leverage tested, maintained infrastructure and idiomatic Laravel patterns.

### User model as token anchor

**Decision**: Use the standard `User` model (not a custom `ApiClient` model) as the Sanctum tokenable.

**Rationale**: The User model is the natural Sanctum anchor and requires no extra configuration. Each consuming app gets a password-less user account (e.g. `yohohoho@apps.ditshej.ch`). This leaves the door open for future extensions: adding an admin user, token scopes, or eventually a real user-facing auth system — without needing a model migration.

**Alternative considered**: Custom `ApiClient` model with `HasApiTokens`. Rejected because it adds an extra model with no benefit over User for this use case, and makes future user-based features harder to add.

### Password-less user accounts

**Decision**: Users are created without passwords (nullable or random `password` field). No login endpoint exists.

**Rationale**: These are machine-to-machine identities, not human accounts. Creating them manually via Artisan command is intentional — no self-registration reduces the attack surface.

### Token displayed once, never again

**Decision**: Plaintext token is shown only in `token:create` output and never stored or retrievable again.

**Rationale**: Security best practice — lost tokens must be regenerated. This mirrors GitHub PATs, Stripe API keys, etc.

## Risks / Trade-offs

- **Migration**: Sanctum requires `personal_access_tokens` table and `users` table. On first deployment, run migrations and then `token:create` for each consuming app. → Mitigation: note in deployment checklist.
- **`API_KEY` in `.env` becomes obsolete**: Harmless if left in, but should be cleaned up. → Mitigation: handled in Cleanup task.
- **Sanctum overhead**: Sanctum adds a `HasApiTokens` trait and token model. For this use case it's minimal, well-tested overhead.

## Migration Plan

1. Install Sanctum, run `vendor:publish`, run migrations
2. `php artisan token:create "Brook Deck Simulator" "brook@apps.ditshej.ch"` per consuming app
3. Set returned plaintext token as `OP_CARDS_API_KEY` in consuming app's `.env`
4. Remove `API_KEY` from `.env`
