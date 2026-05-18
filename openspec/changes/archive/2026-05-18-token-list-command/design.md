## Context

`token:create` and `token:revoke` already use `PersonalAccessToken` from Sanctum directly (no service layer needed for this scale). `token:list` follows the same pattern: one command class, one query, console output.

The existing commands use PHP 8 attribute-style signatures (`#[Signature(...)]`, `#[Description(...)]`), `$this->components->info/warn`, and early returns — `token:list` follows these conventions.

## Goals / Non-Goals

**Goals:**
- `token:list` outputs a readable table by default; `--json` flag for machine-readable output
- `create-token.sh --list` SSHes to the server and calls `php artisan token:list` (mirrors `--revoke` pattern exactly)
- Empty token list: friendly info message, exit `SUCCESS` (not `FAILURE` — empty is valid state)

**Non-Goals:**
- No pagination
- No filtering
- No changes to auth middleware, routes, or models

## Decisions

### Decision: Table output by default, `--json` flag for scripting

**Chosen:** `$this->table([...], $rows)` as default; `$this->line(json_encode($data))` with `--json`.

**Rationale:** `$this->table()` is Laravel's built-in console table helper — no extra dependencies, readable output for operators. `--json` makes the output scriptable (e.g. for the SDK or automated tooling) without requiring `--format` parsers.

**Alternative:** Always output JSON. Rejected — table output is more readable for interactive use.

---

### Decision: Eager-load `tokenable` with column selection

**Chosen:** `PersonalAccessToken::with('tokenable:id,email')->orderByDesc('created_at')->get(['id','name','tokenable_type','tokenable_id','last_used_at','created_at'])`

**Rationale:** Avoids N+1. Column selection keeps the query tight — the table only needs `id`, `name`, `email` (from tokenable), `last_used_at`, and `created_at`. The token hash is intentionally excluded from output.

---

### Decision: Reuse `.env.deploy` in `create-token.sh --list`

**Chosen:** Identical SSH invocation pattern to `--revoke` (lines 15–23 of `create-token.sh`).

**Rationale:** No new credentials needed. Same operator audience as `--revoke`. Keeps the script consistent.

## Risks / Trade-offs

- **Token count**: No pagination added. Acceptable — this API is designed for a small, controlled set of consuming apps. If token count grows significantly, a `LIMIT` query or `--limit` flag can be added.
- **`tokenable` relation**: If a `User` was deleted but the token record remains (orphan), `$token->tokenable` will be `null`. The command should handle this gracefully (display "—" for email).
