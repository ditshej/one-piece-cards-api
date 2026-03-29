## 1. Middleware erstellen & registrieren

- [ ] 1.1 `php artisan make:middleware ApiKeyMiddleware` — Middleware-Klasse erstellen
- [ ] 1.2 `ApiKeyMiddleware::handle()` implementieren: `bearerToken()` gegen `config('auth.api_key')` prüfen, bei Mismatch abort(401)
- [ ] 1.3 `config/auth.php` — `'api_key' => env('API_KEY')` hinzufügen
- [ ] 1.4 `.env` und `.env.example` — `API_KEY=` hinzufügen (`.env` mit echtem Key, `.env.example` als Placeholder)
- [ ] 1.5 `bootstrap/app.php` — Middleware-Alias `api.key` registrieren

## 2. Routes absichern

- [ ] 2.1 `routes/api.php` — `api.key` Middleware auf die `v1`-Gruppe anwenden
- [ ] 2.2 `routes/ai.php` — `->middleware('api.key')` auf die MCP-Route anwenden

## 3. Tests anpassen & ergänzen

- [ ] 3.1 Alle bestehenden API-Tests (`PacksApiTest`, `CardsApiTest`) mit korrektem Auth-Header versehen
- [ ] 3.2 Bestehende MCP-Tests (`McpServerTest`) prüfen — die testen direkt über Server-Klasse, kein HTTP → kein Header nötig
- [ ] 3.3 `ApiDocumentationTest` prüfen — Docs-Routes sind public, kein Header nötig
- [ ] 3.4 Neuen Test: 401 ohne Header für REST-API
- [ ] 3.5 Neuen Test: 401 ohne Header für MCP-Endpoint (HTTP-Level)
