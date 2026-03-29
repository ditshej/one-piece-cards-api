## Context

Aktuell gibt es kein Middleware-Verzeichnis und keine Auth-Middleware. `bootstrap/app.php` hat eine leere `withMiddleware`-Sektion. Der API-Key wird als einziger statischer Wert in `.env` gespeichert — kein User-Model, keine Datenbank.

## Goals / Non-Goals

**Goals:**
- REST-API und MCP mit einem geteilten Key absichern
- Zero-Overhead: kein neues Package, kein DB-Query pro Request
- Einfach rotierbar: Key-Wechsel = `.env` ändern + `optimize:clear`

**Non-Goals:**
- Mehrere Keys / per-client Keys
- Sanctum / Passport
- Rate Limiting

## Decisions

### API-Key in `config/auth.php` statt eigener Config

`config/auth.php` existiert bereits und wird per `config('auth.api_key')` abgerufen. Kein neues Config-File nötig.

### `bearerToken()` statt eigenen Header

Laravel's `$request->bearerToken()` parsed `Authorization: Bearer <key>` automatisch. Standard-HTTP-Auth-Pattern, funktioniert mit MCP-Clients und `curl` gleich.

### Middleware-Alias `api.key`

Statt Middleware global anzuwenden, wird sie als Alias registriert und explizit auf die Routes angewendet. So bleibt `/docs/api*` öffentlich ohne Ausnahme-Logik.

### MCP-Route: `->middleware()` auf dem zurückgegebenen Route-Objekt

`Mcp::web()` gibt ein Laravel `Route`-Objekt zurück — Middleware kann direkt gekettet werden.

## Risks / Trade-offs

- **[Risk] Key im `.env` im Klartext**: Shared-Hosting hat `.env` nicht öffentlich → kein Problem
- **[Risk] Bestehende Tests brechen**: Alle API- und MCP-Tests müssen den Header senden → Update der Tests nötig
