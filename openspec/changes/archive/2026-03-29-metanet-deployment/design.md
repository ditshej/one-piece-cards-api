## Context

The API is fully implemented and tested locally. It needs to be deployed to Metanet Shared Hosting under `op-cards.ditshej.ch`. The reference for this deploy approach is `statamic-ferienspasswil` (same hosting provider, same pattern). Key constraint: SSH credentials must never be committed to the repository.

## Goals / Non-Goals

**Goals:**
- Deploy the API to `https://op-cards.ditshej.ch`
- Create reproducible deploy scripts (`deploy.sh` + `_deploy.sh`)
- Keep SSH credentials out of version control via a gitignored `.env.deploy`

**Non-Goals:**
- CI/CD pipeline or automated deploys
- Database syncing from local to server (Change 8)
- Automated data import on deploy
- Docker or containerisation
- PostgreSQL migration (stays SQLite)

## Decisions

### Two-script deploy pattern (same as `statamic-ferienspasswil`)

`deploy.sh` (local) SSHes into the server and runs `_deploy.sh` (remote). Both committed to the repo.

- **Why**: Simple, transparent, no external tooling required. Easy to debug.
- **Alternative considered**: GitHub Actions — overkill for a personal project, requires secrets management.

### Credentials in `.env.deploy` (gitignored), not hardcoded

`deploy.sh` sources `.env.deploy` for `DEPLOY_SSH_CONNECTION` and `DEPLOY_PATH`. A `.env.deploy.example` template is committed.

- **Why**: The user explicitly requires credentials never be committed. Following the existing `.env` pattern is consistent.
- **Alternative considered**: Hardcode in `deploy.sh` and gitignore that file — worse, because then the deploy logic itself is not versioned.

### No `npm run build` step

This is a pure API — no Vite frontend assets.

### `artisan migrate --force` on every deploy

Migrations are idempotent. Running on every deploy ensures the schema is always up-to-date without manual intervention.

## Risks / Trade-offs

- **SQLite on shared hosting** → SQLite is fine for read-heavy personal use. Concurrent writes are not a concern here.
- **PHP binary path may change** → `_deploy.sh` hardcodes the PHP 8.4 binary path (`/usr/bin/php84`). If Metanet changes paths, the script must be updated manually.
- **No rollback mechanism** → `git pull` cannot be automatically reverted. Manual `git reset` on the server required if a bad deploy happens.
- **First deploy is manual** → Repo clone, `.env` creation, and document root config are one-time manual steps not covered by the scripts.

## Migration Plan

1. SSH into server, verify PHP 8.4 binary path
2. Clone repo into `~/op-cards.ditshej.ch`
3. Set Metanet document root to `~/op-cards.ditshej.ch/public`
4. Create `~/.env` (or `~/op-cards.ditshej.ch/.env`) with production values
5. Run `php artisan key:generate` and `php artisan migrate --force`
6. Create local `.env.deploy` with real credentials
7. Run `bash deploy.sh` for all subsequent deploys

## Open Questions

- Exact PHP 8.4 binary path on Metanet server (verify via SSH: `ls /usr/bin/php*`)
- Composer path on server (`~/bin/composer` — same pattern as `statamic-ferienspasswil`)
