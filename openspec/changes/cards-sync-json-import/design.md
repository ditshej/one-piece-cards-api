## Context

The root cause is coupling at the storage layer: `packs`/`cards` share one SQLite file with `users`/`sessions`/`personal_access_tokens`/`cache`/`jobs`, and `cards:sync` copies that file wholesale. Rather than split the database (invasive: new connection, `$connection` on models, migrating existing production data, cross-DB FK removal), we change *what* gets synced.

The project already has a complete, idempotent card-ingestion pipeline: `cards:fetch` (vegapull → JSON) → `cards:import` (JSON → `Pack`/`Card` via `updateOrCreate`). `cards:import` reads from `config('import.vegapull_path').'/json'` (= `storage/vegapull/json/`), creates each pack before its cards (FK-safe), and only ever writes the `packs`/`cards` tables. Production almost certainly lacks the vegapull/cargo toolchain — which is the only reason the DB file was pushed in the first place — but it *can* run `cards:import` against JSON files we ship to it.

## Goals / Non-Goals

**Goals:**
- `cards:sync` moves only card data to production, reusing `cards:import`
- Auth/session/token/cache/job tables are structurally impossible to overwrite via sync
- Keep the command's existing shape: config check, optional `--fetch`, remote cache clear, array-style `Process::run`, `$this->info/error/warn` output

**Non-Goals:**
- Delete/prune of upstream-removed cards
- Separate card database
- Any change to `cards:import`/`cards:fetch` behaviour

## Decisions

### Decision: Upload JSON + remote `cards:import` instead of SCP-ing the SQLite file

**Chosen:** `cards:sync` uploads `storage/vegapull/json/` to `{path}/storage/vegapull/json/` on production, then runs `cd {path} && /opt/php83/bin/php artisan cards:import` over SSH.

**Rationale:** Reuses the tested idempotent pipeline; card data is the only thing in the transfer path, so the auth-overwrite bug class is eliminated by construction. Zero schema changes, smallest diff.

**Alternatives rejected:**
- *Separate SQLite DB for cards* — clean separation but invasive (new connection, model `$connection`, migrate existing prod data, relocate FK). Overkill for a single-domain app.
- *Selective `sqlite3 .dump packs cards`* — exact replica incl. deletes, but shell-fragile, manual FK ordering, no app-code reuse.

### Decision: Ensure remote target directory exists before SCP

**Chosen:** `ssh … "mkdir -p {path}/storage/vegapull/json"` before the `scp -r`.

**Rationale:** A fresh production checkout may not have `storage/vegapull/json/`; `scp -r` into a missing parent path fails. `mkdir -p` is idempotent and cheap.

### Decision: Preconditions — fail before any remote action

**Chosen:** Keep the existing config guard (`sync_host`/`sync_user`/`sync_path`). Replace the `database.sqlite` existence check with a check that the local JSON directory exists and contains `packs.json` / `cards_*.json`; on failure exit `FAILURE` before any SCP/SSH.

**Rationale:** Fail fast and locally. Never open an SSH/SCP connection when there is nothing valid to send.

### Decision: Make the remote PHP binary configurable via `SYNC_PHP`

**Chosen:** Add `'sync_php' => env('SYNC_PHP', 'php')` to `config/import.php`; the command builds remote `artisan` calls with `config('import.sync_php')` instead of the literal `/opt/php83/bin/php`. Document `SYNC_PHP` in `.env.example`.

**Rationale:** A configurable PHP path is already the convention: `create-token.sh` uses `${DEPLOY_PHP:-php}` and `laravel-ai-boilerplate` ships `DEPLOY_PHP=php` in `.env.deploy.example`. `cards:sync` is the only place still hardcoding it. Keeping the key in the `SYNC_*` family (read via `config('import.*')`) matches how `cards:sync` already sources its config; an Artisan command cannot read `.env.deploy` (shell-only), so reusing `DEPLOY_PHP` there would be misleading. Default `php` mirrors the boilerplate default — operators set `SYNC_PHP=/opt/php83/bin/php` in production `.env`.

**Alternative rejected:** Reuse the `DEPLOY_PHP` name in `config/import.php`. Rejected — it would read from `.env`, not `.env.deploy`, making the shared name misleading. Fully unifying the two config sources is out of scope.

### Decision: Keep `--fetch` and remote `optimize:clear` unchanged

**Chosen:** `--fetch` still runs `cards:fetch` locally first; after the import, still run `optimize:clear` remotely (warn-only on failure).

**Rationale:** `--fetch` refreshes the local JSON that then gets shipped — semantics stay intuitive. Clearing the production cache after a data change remains correct.

## Risks / Trade-offs

- **Upstream deletes not propagated** — `updateOrCreate` never deletes, so a card removed at Bandai lingers on production. Accepted as a non-goal; card removals are rare and low-impact. A future `--prune` could diff-and-delete inside a transaction.
- **Two-step remote failure** — SCP could succeed while the remote import fails, leaving fresh JSON but stale DB. The command surfaces the import's non-zero exit as `FAILURE` so the operator can re-run; card data stays internally consistent because import is idempotent.
- **`SYNC_PHP` must be set locally** — `cards:sync` runs on the operator's machine and passes the PHP path into the SSH command, so `SYNC_PHP` is read from the **local** `.env` (it names the *remote* server's PHP binary). The default is `php` (boilerplate convention); the local `.env` is set to `SYNC_PHP=/opt/php83/bin/php` as part of this change so the current sync keeps working. Any new operator/environment running `cards:sync` must set it too, or the remote `artisan` calls may hit the wrong PHP.
- **Dual config source** — `cards:sync` (`.env`/`SYNC_*`) and `create-token.sh` (`.env.deploy`/`DEPLOY_*`) target the same server via different config. Not unified here; noted as future cleanup.
