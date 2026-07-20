## Context

`_deploy.sh` is a small POSIX shell script (`#!/bin/sh`, `set -e`) that runs on the Metanet server. It pulls the latest code, installs production dependencies, migrates, and clears caches. The recent incident showed that its very first step — `git pull origin main` — assumes the server is already on `main`. When the server was on a stale branch (`feat/metanet-deployment`), the pull attempted a merge into that branch and failed with "divergent branches", a message that does not point at the real cause. Recovery required manual SSH intervention.

## Goals / Non-Goals

**Goals:**
- Turn the "wrong branch" / "divergent branches" failure into a clear, actionable error.
- Prevent accidental merge commits on the server (deploys must always be fast-forwards of `origin/main`).
- Keep the script minimal, POSIX-compatible, and fail-fast (`set -e` preserved).

**Non-Goals:**
- Automatically repairing server state (branch switching, hard reset). This is destructive and explicitly disallowed by the repo's safety rules; a tripped guard is fixed manually.
- Any CI/CD, rollback, or provisioning changes.

## Decisions

### Fail-fast guard instead of auto-switching to `main`

Before pulling, `_deploy.sh` verifies two preconditions and aborts with a descriptive message if either fails:
1. Current branch is `main` (`git rev-parse --abbrev-ref HEAD`).
2. Working tree is clean (`git diff-index --quiet HEAD --`).

- **Why:** A deploy should never silently mutate server state. Naming the actual branch and the exact fix command (`git checkout main`) makes recovery obvious. Auto-switching could mask an intentional or unexpected server state and, if combined with a reset, would risk discarding an emergency hotfix.
- **Alternative considered — `git checkout main` before pull:** rejected as the default because it hides *why* the server was off `main` and can itself fail confusingly when the tree is dirty.
- **Alternative considered — `git fetch && git reset --hard origin/main`:** rejected. `git reset --hard` is destructive and disallowed by the project safety rules.

### `git pull --ff-only origin main`

- **Why:** With the branch guard in place, the only remaining divergence risk is a `main` that has local commits ahead of `origin/main`. `--ff-only` makes that fail explicitly ("Not possible to fast-forward") and guarantees no merge commit is ever created on the server.

### Manual test plan instead of automated tests

- **Why:** `_deploy.sh` is pure shell with no test harness in the project, and its behavior depends on a live git checkout on the remote host. A documented manual test plan (simulate wrong branch → expect clear abort; simulate dirty tree → expect clear abort; clean `main` → expect successful pull) is the pragmatic verification. This matches how the original `metanet-deployment` change was verified.

## Risks / Trade-offs

- **Guard trips still require manual SSH fix** → acceptable and intended: the message tells the operator exactly what to run, which is far better than the previous cryptic failure.
- **`--ff-only` will fail if the server has local commits on `main`** → this is desirable: it surfaces an unexpected state rather than creating a merge commit.

## Migration Plan

No migration. The next `bash deploy.sh` picks up the new `_deploy.sh` automatically after it is pulled. (For the very first deploy of this change, the server must already be on `main` — which is now the case after the manual recovery.)

## Open Questions

<!-- none -->
