## Why

`cards:sync` uploads the **entire** `database/database.sqlite` file to production via SCP (`SyncCardsCommand.php:44`). That single SQLite file holds far more than card data: `users`, `sessions`, and `personal_access_tokens` (Sanctum) live there too, plus `cache` and `jobs` (because `SESSION_DRIVER`, `QUEUE_CONNECTION`, and `CACHE_STORE` are all `database`). Every sync therefore overwrites production's auth state with the local copy.

This already caused a production incident: the last sync destroyed the production MCP Sanctum token, breaking authenticated access. Any token created on production (`token:create`) is wiped by the next sync. The sync mechanism must never touch auth data.

## What Changes

- **`cards:sync` no longer transfers the SQLite file.** Instead it uploads only the vegapull card JSON files (`storage/vegapull/json/`) to production, then runs `php artisan cards:import` remotely over SSH. This reuses the existing idempotent `updateOrCreate` import pipeline and keeps card data as the only thing that moves.
- Auth/session/token tables are never in the transfer path, so syncing can no longer clobber production credentials.
- **Make the production PHP binary configurable.** The command currently hardcodes `/opt/php83/bin/php` for the remote `artisan` calls. Add `'sync_php' => env('SYNC_PHP', 'php')` to `config/import.php` and use `config('import.sync_php')` instead. Default `php` matches the `DEPLOY_PHP=php` convention already used by `create-token.sh` and `laravel-ai-boilerplate`.
- Update `openspec/specs/data-sync/spec.md`: the sync requirement changes from "uploads SQLite DB" to "uploads card JSON and imports remotely".

**Non-goals:**
- No delete/prune propagation — cards removed upstream at Bandai stay on production (`updateOrCreate` does not delete). Acceptable for card data; can be added later if needed.
- No separate SQLite database for card vs auth data (a heavier alternative that was considered and rejected in favour of this smaller fix).
- No unification of the two config sources for the same server (`cards:sync` reads `SYNC_*` from `.env`; `create-token.sh` reads `DEPLOY_*` from `.env.deploy`). This pre-existing split stays; only the PHP-path hardcode is fixed here.
- No changes to `cards:import`, `cards:fetch`, the DB schema, or any model.
- No changes to authentication middleware or API routes.

## Capabilities

### Modified Capabilities
- `data-sync`: `cards:sync` transfers vegapull JSON and triggers a remote `cards:import` instead of SCP-ing the whole SQLite file; production auth data is no longer overwritten.

## Impact

- **Modified file**: `app/Console/Commands/SyncCardsCommand.php` (replace whole-file SCP with JSON upload + remote import; use `config('import.sync_php')`)
- **Modified file**: `config/import.php` (add `sync_php` key)
- **Modified file**: `.env.example` (document `SYNC_PHP`)
- **New/updated test file**: `tests/Feature/Commands/SyncCardsCommandTest.php` (assert no `.sqlite` is ever transferred; assert the configured PHP binary is used)
- **Modified spec**: `openspec/specs/data-sync/spec.md`
- **No breaking changes** to the public API — this is an internal ops command
- **Behaviour change for operators**: `cards:sync` now updates production card data in place rather than replacing the DB; production users/tokens/sessions survive a sync
