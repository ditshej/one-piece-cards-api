## 1. Create & register middleware

- [x] 1.1 `php artisan make:middleware ApiKeyMiddleware` — create middleware class
- [x] 1.2 Implement `ApiKeyMiddleware::handle()`: check `bearerToken()` against `config('auth.api_key')`, abort(401) on mismatch
- [x] 1.3 `config/auth.php` — add `'api_key' => env('API_KEY')`
- [x] 1.4 `.env` and `.env.example` — add `API_KEY=` (`.env` with real key, `.env.example` as placeholder)
- [x] 1.5 `bootstrap/app.php` — register middleware alias `api.key`

## 2. Secure routes

- [x] 2.1 `routes/api.php` — apply `api.key` middleware to the `v1` group
- [x] 2.2 `routes/ai.php` — apply `->middleware('api.key')` to the MCP route

## 3. Update & add tests

- [x] 3.1 Add correct auth header to all existing API tests (`PacksApiTest`, `CardsApiTest`)
- [x] 3.2 Check existing MCP tests (`McpServerTest`) — they test directly via server class, no HTTP → no header needed
- [x] 3.3 Check `ApiDocumentationTest` — docs routes are public, no header needed
- [x] 3.4 New test: 401 without header for REST API
- [x] 3.5 New test: 401 without header for MCP endpoint (HTTP level)
