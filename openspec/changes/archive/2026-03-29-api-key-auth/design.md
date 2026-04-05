## Context

Currently there is no middleware directory and no auth middleware. `bootstrap/app.php` has an empty `withMiddleware` section. The API key is stored as a single static value in `.env` — no user model, no database.

## Goals / Non-Goals

**Goals:**
- Secure REST API and MCP with a shared key
- Zero overhead: no new package, no DB query per request
- Easy to rotate: key change = change `.env` + `optimize:clear`

**Non-Goals:**
- Multiple keys / per-client keys
- Sanctum / Passport
- Rate limiting

## Decisions

### API key in `config/auth.php` instead of a separate config

`config/auth.php` already exists and is accessed via `config('auth.api_key')`. No new config file needed.

### `bearerToken()` instead of a custom header

Laravel's `$request->bearerToken()` parses `Authorization: Bearer <key>` automatically. Standard HTTP auth pattern, works the same with MCP clients and `curl`.

### Middleware alias `api.key`

Instead of applying middleware globally, it is registered as an alias and applied explicitly to routes. This keeps `/docs/api*` public without exception logic.

### MCP route: `->middleware()` on the returned route object

`Mcp::web()` returns a Laravel `Route` object — middleware can be chained directly.

## Risks / Trade-offs

- **[Risk] Key in `.env` in plain text**: Shared hosting does not expose `.env` publicly → not a problem
- **[Risk] Existing tests break**: All API and MCP tests must send the header → update of tests required
