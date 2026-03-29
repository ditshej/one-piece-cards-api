## Why

The One Piece Cards API is complete locally but not accessible to other projects (e.g. the Brook OP15 Deck Simulator). Deploying to Metanet Shared Hosting under `op-cards.ditshej.ch` makes the API available for consumption.

## What Changes

- Add `_deploy.sh` — remote deploy script committed to the repo (git pull, composer install, migrate, optimize)
- Add `deploy.sh` — local script that SSH-es into Metanet and runs `_deploy.sh`; reads credentials from a gitignored `.env.deploy`
- Add `.env.deploy.example` — committed template for SSH credentials
- Update `.gitignore` to exclude `.env.deploy`
- Initial server setup: clone repo, create `.env`, set document root to `public/`

## Capabilities

### New Capabilities
- `deployment`: Deploy scripts and server setup for Metanet Shared Hosting

### Modified Capabilities
<!-- none — no existing spec requirements change -->

## Impact

- No API changes, no route changes
- New files: `deploy.sh`, `_deploy.sh`, `.env.deploy.example`
- `.gitignore` updated
- Production environment: `op-cards.ditshej.ch` on Metanet (PHP 8.4, Apache, SQLite)
- Affected endpoints: all (now publicly reachable at `https://op-cards.ditshej.ch/v1/*`)
- Non-goals: no CI/CD pipeline, no automated scheduled deploys, no Docker, no database syncing (Change 8)
