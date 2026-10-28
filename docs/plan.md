# Plan: Move op-cards.ditshej.ch → cards.optcg.ch

Status: complete, accepted by ditshej (plan revision 9, 2026-10-28; revision 8 accepted 2026-10-26). No open points.

## Goal

The One Piece Cards API (REST under `/api/v1`, API docs under `/docs/api`, MCP server under `/mcp`) will run at **https://cards.optcg.ch**. `op-cards.ditshej.ch` is shut down, deleted and removed from DNS.

## Current state (from the repo and a live check, 2026-10-26)

- **Hosting:** Metanet Plesk server (IP 80.74.158.100), site `op-cards.ditshej.ch`, checkout in `/op-cards.ditshej.ch`, document root `/op-cards.ditshej.ch/public`. Running: `/up` returns 200.
- **Deploy:** `./deploy.sh` on the Mac builds the assets, uploads `public/build` via rsync and starts `_deploy.sh` on the server (git pull --ff-only, composer install, migrate, optimize:clear). Credentials in `.env.deploy` (not in the repo).
- **Data:** SQLite (`database/database.sqlite` on the server). Contains the card data **and** the API tokens (Sanctum, one user per client app), sessions, cache, queue.
- **Card data update:** `php artisan cards:sync` (local) uploads JSON and starts `cards:import` on the server. Target via `SYNC_*` in the local `.env`. A weekly scheduler job (`cards:import`) exists, but is only active with `IMPORT_SCHEDULE_ENABLED=true` (off today).
- **Tokens:** `./create-token.sh` manages tokens via SSH (reads `.env.deploy`).
- **DNS:** `cards.optcg.ch` already points to the same server (80.74.158.100), but has no matching TLS certificate and no site yet.
- **Clients:** only ditshej's own apps. None of them is live.
- **Repo:** The README names `op-cards-api.ditshej.ch` (wrong host, no valid certificate; the real URL is `op-cards.ditshej.ch`). `tests/Feature/Commands/SyncCardsCommandTest.php` uses the path `/op-cards.ditshej.ch` as a test value. There is no deploy documentation (`docs/deployment.md`) yet. `.env.example` and `.env.deploy.example` contain placeholders only.

## Decided

1. **New Plesk site `cards.optcg.ch`** (no alias). Own checkout in `/cards.optcg.ch`, document root `/cards.optcg.ch/public`, own Let's Encrypt certificate, own `.env` on the server. Locally, new values go into `.env.deploy` and `SYNC_*` (path and host of the new site).
2. **`op-cards.ditshej.ch` is shut down** (no redirect). Anyone calling the old URL afterwards gets an error.
3. **Shutdown immediately at the switch** (no transition period, no parallel operation).
4. **Clients:** There are only ditshej's own apps, none of them is live. No client is switched over before the shutdown. ditshej sets up an app again when it is needed (new URL, new token).
5. **Fresh start instead of data migration:** The SQLite database of the old site is **not** copied. The new site starts with an empty database (`migrate`). Card data comes to the new site via `php artisan cards:sync`. Old tokens, users and sessions expire with the old site. New tokens only when needed via `./create-token.sh`.
6. **Downtime:** does not matter, because no clients are live. No time window needed.
7. **Order:**
   0. Repo changes (see 11) as a pull request; ditshej merges it before step 2.
   1. Create the Plesk site `cards.optcg.ch`, Let's Encrypt certificate, checkout, `.env` on the server.
   2. Locally set `.env.deploy` and `SYNC_*` to the new site, run `./deploy.sh`.
   3. `php artisan cards:sync` to the new site.
   4. No Plesk cron for `schedule:run` (see 9).
   5. Check: `/up`, `/api/v1` with a new test token, `/docs/api`, `/mcp`.
   6. Only after a successful check: **delete** the site `op-cards.ditshej.ch` in Plesk and **remove** the DNS record `op-cards.ditshej.ch` (see 10).
8. **Rollback:** Until step 6 the old site keeps running unchanged. If something goes wrong on the new site, step 6 is not executed; the new site is repaired or removed again in Plesk. After step 6 there is **no way back** to the old site. Errors on `cards.optcg.ch` are then fixed on the new site (deploy a fix or deploy an earlier commit, run `cards:sync` again). This is accepted, because no data of the old site is needed (see 5).
9. **No weekly import.** The scheduler job `cards:import` only imports the JSON files that are already on the server (`storage/vegapull`). `cards:sync` already starts the import itself after the upload. A weekly run therefore adds nothing. In the server `.env` of `cards.optcg.ch`: `IMPORT_SCHEDULE_ENABLED=false`. No Plesk cron for `schedule:run` (there is no other scheduler job). New card data only comes via `cards:sync` from the Mac. The code (`routes/console.php`) stays unchanged. (Changed on 2026-10-28; revision 8 had the weekly import.)
10. **Old site is deleted, DNS record removed.** Directly after a successful check (step 6): delete the site `op-cards.ditshej.ch` in Plesk (with checkout and database) and remove the DNS record `op-cards.ditshej.ch`. No backup of the old database. The domain `ditshej.ch` and its other records stay unchanged. ditshej does both.
11. **Repo changes** (one pull request, docs and test value only, no functional change, therefore no OpenSpec change):
    - `README.md`: live URL and docs link to `https://cards.optcg.ch` (replaces the wrong host `op-cards-api.ditshej.ch`).
    - `docs/deployment.md` (new): setup of the site `cards.optcg.ch` in Plesk (document root, certificate, `.env` with `IMPORT_SCHEDULE_ENABLED=false`), note: no cron for `schedule:run`, local `.env.deploy` and `SYNC_*`, `./deploy.sh`, `cards:sync`, `./create-token.sh`, check steps. No real credentials.
    - `tests/Feature/Commands/SyncCardsCommandTest.php`: test path `/op-cards.ditshej.ch` → `/cards.optcg.ch`.
    - Archived OpenSpec changes (`openspec/changes/archive/`) stay unchanged; they describe the history.
12. **Done criteria:** The move is done when everything is verifiably completed:
    - The pull request with the repo changes (see 11) is merged, as is the follow-up pull request for point 9 (`docs/plan.md`, `docs/deployment.md`).
    - On `https://cards.optcg.ch`, `/up` (200), `/api/v1` with a test token, `/docs/api` and `/mcp` work; the certificate is valid.
    - On the server: `IMPORT_SCHEDULE_ENABLED=false` in the `.env`, no scheduled task for `schedule:run` in Plesk.
    - The site `op-cards.ditshej.ch` is deleted in Plesk, and the DNS record `op-cards.ditshej.ch` is removed (the name no longer resolves).

## Open

None.
