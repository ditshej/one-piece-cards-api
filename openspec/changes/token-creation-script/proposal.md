## Why

After the `multi-token-auth` change, the API requires a Bearer token but there is no project README explaining how to use it, and the Scramble API docs at `/docs/api` don't explicitly explain the token acquisition process for consumers. Additionally, issuing tokens still requires manual SSH access — a local convenience script closes this gap.

## What Changes

- New shell script `create-token.sh`: reads SSH credentials from `.env.deploy`, SSHes into the server, and runs `php artisan token:create {name} {email}` remotely, printing the plaintext token locally
- Updated `README.md`: replace the default Laravel README with a project-specific one covering API usage (base URL, auth, endpoints), deployment (`./deploy.sh`), and token management (`./create-token.sh`)
- Updated Scramble API description (`config/scramble.php` `info.description`): explicitly explain that consumers must contact the owner to obtain an API key, and document the Bearer token format

**Non-goals:**
- No changes to the Sanctum middleware or authentication flow
- No new API endpoints for token management
- No token revocation script (manual Tinker operation for now)
- No changes to `docs/dev-setup.md`

## Capabilities

### New Capabilities
*(none — the script is operational tooling, not a new API capability)*

### Modified Capabilities
- `token-management`: Add requirement for `create-token.sh` (local SSH wrapper for `php artisan token:create`), a project README, and an improved API documentation description explaining token acquisition

## Impact

- **New file**: `create-token.sh` (local script, executable)
- **Modified file**: `README.md` (replaced with project-specific content)
- **Modified config**: `config/scramble.php` `info.description` field
- **`.gitignore`**: already excludes `.env.deploy` (shared with `deploy.sh`)
- **No breaking changes** — purely additive tooling and documentation
- **No API changes**
