## Why

`_deploy.sh` runs `git pull origin main` blindly, without ensuring the server is actually on the `main` branch first. During a real deploy the server was still checked out on an old branch (`feat/metanet-deployment`), so `git pull origin main` tried to merge `origin/main` into that branch and failed with a cryptic "divergent branches" error. The deploy had to be fixed manually over SSH (`git checkout -B main origin/main`, then composer/migrate/optimize by hand). The deploy script should turn this failure mode into a clear, actionable error instead of a confusing one.

## What Changes

- Update `_deploy.sh` to guard the state of the server working tree before pulling:
  - Abort with a clear message if the server is not on the `main` branch (naming the actual branch and the fix command).
  - Abort with a clear message if the working tree has uncommitted changes.
  - Use `git pull --ff-only origin main` so a diverged `main` fails explicitly as "not a fast-forward" rather than attempting a merge commit on the server.
- Keep `set -e` intact so any step failure still aborts the deploy.
- No change to `deploy.sh` (local orchestration) or the composer/migrate/optimize steps.

## Capabilities

### New Capabilities
<!-- none -->

### Modified Capabilities
- `deployment`: the remote deploy script now guards branch and working-tree state before pulling

## Impact

- No API changes, no route changes, no database changes
- Modified file: `_deploy.sh`
- Affected workflow: `bash deploy.sh` (which invokes `_deploy.sh` on the server)
- Non-goals:
  - No automatic branch switching or `git reset --hard` on the server (destructive, disallowed) — a tripped guard is fixed manually over SSH.
  - No CI/CD pipeline, no automated rollback, no Docker.
  - No change to server provisioning or document-root setup.
