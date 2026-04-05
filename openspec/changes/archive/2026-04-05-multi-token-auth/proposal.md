## Why

The API is consumed by multiple apps (Brook OP15 Deck Simulator, future projects via `ditshej/op-cards-php` SDK). A single API key in `.env` cannot be revoked per app and provides no identity for individual clients. Multi-token authentication with a User model gives each consuming app its own identity, enables individual revocation, and leaves the door open for future external consumers (e.g. a friend's low-traffic project).

## What Changes

- Standard Laravel `users` table with password-less user accounts representing consuming apps
- Laravel Sanctum Personal Access Tokens bound to these user accounts
- Existing `.env`-based authentication (`API_KEY`) replaced by Sanctum token validation
- Artisan command `token:create {name} {email}` creates a user + issues a token, displays plaintext once
- `AuthenticateWithSanctum` middleware (or Sanctum's built-in `auth:sanctum` guard) protects API routes

**Non-goals:**
- No login flow, no password-based auth, no registration endpoint
- No token expiry (for now)
- No rate limiting per token
- No admin UI for token management
- No public self-registration for external users

## Capabilities

### New Capabilities
- `token-management`: Artisan command to create user-bound API tokens; Sanctum-backed token storage with name, hash, `last_used_at`

### Modified Capabilities
- `api-key-auth`: Token validation switches from `.env` value to Sanctum DB lookup; behavior (401/200) stays identical, token format stays `Authorization: Bearer <token>`

## Impact

- **New dependency**: `laravel/sanctum` (official Laravel package)
- **New table**: `personal_access_tokens` (Sanctum), `users` (standard Laravel)
- **Affected routes**: `api/v1/*`, `POST /mcp`
- **No breaking change** for clients: interface (`Authorization: Bearer <token>`) remains identical
- **`.env`**: `API_KEY` becomes obsolete and can be removed
