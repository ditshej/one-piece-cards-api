## 1. Sanctum Setup

- [ ] 1.1 Install `laravel/sanctum` and publish configuration
- [ ] 1.2 Run Sanctum migrations (`personal_access_tokens` table)
- [ ] 1.3 Ensure `users` table migration exists (standard Laravel)
- [ ] 1.4 Add `HasApiTokens` trait to `User` model

## 2. Artisan Command

- [ ] 2.1 Create `token:create {name} {email}` command: create password-less User, issue Sanctum token, display plaintext once

## 3. Middleware & Route Protection

- [ ] 3.1 Apply `auth:sanctum` guard to `api/v1/*` routes
- [ ] 3.2 Apply `auth:sanctum` guard to `POST /mcp` route
- [ ] 3.3 Remove `ApiKeyMiddleware` and its registration

## 4. Tests

- [ ] 4.1 Feature test for API routes: no token → 401, invalid token → 401, valid Sanctum token → 200
- [ ] 4.2 Feature test for MCP route: same behavior as API routes
- [ ] 4.3 Feature test for `token:create`: user created, token stored, plaintext output shown
- [ ] 4.4 Feature test: `last_used_at` updated on valid request

## 5. Cleanup

- [ ] 5.1 Remove `API_KEY` from `config/auth.php` and `.env.example`
- [ ] 5.2 Delete `ApiKeyMiddleware` class
- [ ] 5.3 Run Pint and ensure all tests pass
