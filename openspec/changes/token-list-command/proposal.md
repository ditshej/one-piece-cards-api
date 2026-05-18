## Why

After the `token-creation-script` change, operators can create and revoke tokens via `create-token.sh` and `php artisan token:revoke`. However, there is no way to inspect which tokens currently exist — neither locally nor on the production server — without direct database access or Tinker. Before tagging v0.1.0, the token management toolset should be complete with a list command to close this gap.

## What Changes

- New Artisan command `token:list`: displays all Sanctum Personal Access Tokens in a table (ID, name, user email, last used, created at), with an optional `--json` flag for scripting
- Updated `create-token.sh`: adds a `--list` flag that SSHes to the server and runs `php artisan token:list`, consistent with the existing `--revoke` flag
- Updated `README.md`: documents `token:list` and `./create-token.sh --list` in the Token Management section
- Updated `openspec/specs/token-management/spec.md`: adds requirements and scenarios for token listing

**Non-goals:**
- No pagination (token count will always be small for this project)
- No filtering by app name or date
- No changes to authentication middleware or API routes
- No token creation or revocation in this command

## Capabilities

### Modified Capabilities
- `token-management`: Add `token:list` Artisan command and `--list` flag in `create-token.sh`; extend README Token Management section

## Impact

- **New file**: `app/Console/Commands/TokenList.php`
- **New test file**: `tests/Feature/Commands/TokenListTest.php`
- **Modified file**: `create-token.sh` (add `--list` branch + usage text)
- **Modified file**: `README.md` (Token Management section)
- **Modified spec**: `openspec/specs/token-management/spec.md`
- **No breaking changes** — purely additive
- **No API changes**
