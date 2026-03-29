## Context

The local SQLite DB (`database/database.sqlite`) contains all card data imported via `cards:fetch`. The production server at `op-cards.ditshej.ch` needs this DB. Currently it was uploaded manually via SCP. `cards:sync` automates this as a repeatable Artisan command.

Server details: `www-data@goethe.metanet.ch -p 2121` (chrooted, home = `/`). Target path: `/op-cards.ditshej.ch/database/database.sqlite`.

## Goals / Non-Goals

**Goals:**
- Upload local SQLite DB to server via SCP
- Optional `--fetch` flag to run `cards:fetch` first
- Clear production cache after sync (`artisan optimize:clear` via SSH)
- Config-driven SSH credentials (never hardcoded)

**Non-Goals:**
- Two-way sync or incremental sync
- Backup of remote DB before overwrite
- Running migrations remotely (already handled by `deploy.sh`)

## Decisions

### Config in `config/import.php`, not a new config file
Sync is part of the import/data pipeline — consistent with existing `vegapull_*` keys.

### SCP via `symfony/process` (already available via Laravel)
No new dependencies. Run `scp` as a shell process, same pattern as `cards:fetch` uses for `vega`.

### SSH port as separate config key
Metanet uses port 2121. Keeping host, user, port, path as separate keys is cleaner than parsing a connection string.

### Cache clear via SSH after sync
After uploading the DB, run `ssh ... artisan optimize:clear` to ensure production serves fresh data. Uses the same SSH credentials.

## Risks / Trade-offs

- **Overwrites production DB without backup** → Acceptable for a personal project; local DB is always the source of truth
- **SCP requires SSH access** → Same credentials as deploy, already working
- **Chrooted path** → `/op-cards.ditshej.ch/...` not `~/op-cards.ditshej.ch/...` (learned from deploy setup)
