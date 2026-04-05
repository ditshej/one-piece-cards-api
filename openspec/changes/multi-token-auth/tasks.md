## 1. Sanctum Setup

- [x] 1.1 Install `laravel/sanctum` and publish configuration
- [x] 1.2 Run Sanctum migrations (`personal_access_tokens` table)
- [x] 1.3 Ensure `users` table migration exists (standard Laravel)
- [x] 1.4 Add `HasApiTokens` trait to `User` model

## 2. Artisan Command

- [x] 2.1 Create `token:create {name} {email}` command: create password-less User, issue Sanctum token, display plaintext once

## 3. Middleware & Route Protection

- [x] 3.1 Apply `auth:sanctum` guard to `api/v1/*` routes
- [x] 3.2 Apply `auth:sanctum` guard to `POST /mcp` route
- [x] 3.3 Remove `ApiKeyMiddleware` and its registration

## 4. Tests

- [x] 4.1 Feature test for API routes: no token → 401, invalid token → 401, valid Sanctum token → 200
- [x] 4.2 Feature test for MCP route: same behavior as API routes
- [x] 4.3 Feature test for `token:create`: user created, token stored, plaintext output shown
- [x] 4.4 Feature test: `last_used_at` updated on valid request

## 5. Cleanup

- [x] 5.1 Remove `API_KEY` from `config/auth.php` and `.env.example`
- [x] 5.2 Delete `ApiKeyMiddleware` class
- [x] 5.3 Run Pint and ensure all tests pass
