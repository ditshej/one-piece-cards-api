## Context

The `multi-token-auth` change introduced `php artisan token:create` and Sanctum Bearer auth. Three gaps remain:

1. **Token issuance**: requires manual SSH — `create-token.sh` wraps this, following the same pattern as `deploy.sh` (reads `.env.deploy`)
2. **Consumer-facing docs** (`/docs/api`): the Scramble description already appends a contact line from `.env` via `AppServiceProvider`, but the base description in `config/scramble.php` doesn't mention Bearer auth or how to request access — consumers see "Bearer token required" in the UI but no guidance
3. **README**: still the default Laravel boilerplate — no project identity, no API usage, no operational runbook

## Goals / Non-Goals

**Goals:**
- `create-token.sh` reuses `.env.deploy` (no extra credential file), follows `deploy.sh` pattern
- `config/scramble.php` `info.description` extended with auth explanation and token acquisition instruction
- `README.md` replaced with a project-specific document: purpose, base URL, auth, endpoint overview, deploy, token creation

**Non-Goals:**
- No token revocation or listing scripts
- No changes to auth middleware or API routes
- No changes to `docs/dev-setup.md`

## Decisions

### Decision: Reuse `.env.deploy` for SSH credentials in create-token.sh

**Chosen:** `create-token.sh` sources `.env.deploy` — the same file `deploy.sh` uses.

**Rationale:** No second credential file. `DEPLOY_USER`, `DEPLOY_HOST`, `DEPLOY_PORT`, `DEPLOY_PATH` already exist and are gitignored. Token issuance is an operator task, same audience as deployment.

**Alternative:** Separate `.env.tokens` file. Rejected — unnecessary for a project this small.

---

### Decision: Extend Scramble description in config, not in AppServiceProvider

**Chosen:** Update `info.description` in `config/scramble.php` directly.

**Rationale:** The contact-line injection in `AppServiceProvider` is dynamic (env-driven, conditional). The auth explanation is static and belongs with the static config. Both compose naturally in the final rendered description.

---

### Decision: README replaces Laravel boilerplate entirely

**Chosen:** Full replacement — project name, purpose, base URL, auth, endpoints, deploy, token creation.

**Rationale:** The Laravel default README has no value for this project's consumers or operators. A clean project README is more useful than a hybrid.

## Risks / Trade-offs

- **`config/scramble.php` `info.description` change**: the AppServiceProvider appends to whatever is in this field — the result must read coherently. Verify rendered output after change.
- **README content**: base URL and endpoint list can drift as the API evolves. Keep it high-level (link to `/docs/api` for full reference).
