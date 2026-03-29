## Why

The API is live on Metanet but the SQLite database was uploaded manually. There is no repeatable way to sync local card data to production. `cards:sync` automates this: one command to upload the local DB and optionally fetch fresh data first.

## What Changes

- Add Artisan command `cards:sync` — uploads the local SQLite DB to the Metanet server via SCP, then clears the production cache
- Optional `--fetch` flag to run `cards:fetch` before syncing (pull latest card data, then sync)
- SSH/SCP config read from `.env` (same credentials as `.env.deploy`, but via config keys)

## Capabilities

### New Capabilities
- `data-sync`: Artisan command to sync the local SQLite database to the production server

### Modified Capabilities
<!-- none -->

## Impact

- New file: `app/Console/Commands/SyncCardsCommand.php`
- New config keys in `config/import.php`: `sync_host`, `sync_user`, `sync_port`, `sync_path`
- `.env.example` updated with new sync variables
- No API changes, no route changes
- Non-goals: no two-way sync, no incremental sync, no backup before overwrite
