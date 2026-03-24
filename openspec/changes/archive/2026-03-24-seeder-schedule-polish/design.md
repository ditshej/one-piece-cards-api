## Context

The core data layer (Pack/Card models, migrations, factories), import pipeline (`cards:import`), and API endpoints are all implemented and tested. The project is missing three pieces of operational polish:

1. The `DatabaseSeeder` only creates a test user — no card/pack data for local development.
2. The `cards:import` command must be run manually; there's no automated schedule.
3. No architecture tests enforce project conventions (no `env()` outside config, no `dd()`).

The existing import spec already defines "Scheduled Import" and "Vegapull Integration" requirements. This change implements the scheduled import (config-gated) and defers vegapull integration.

## Goals / Non-Goals

**Goals:**
- DatabaseSeeder creates sample packs and cards using existing factories for local development.
- Weekly scheduled `cards:import` registered in `routes/console.php`, disabled by default via `config('import.schedule_enabled')`.
- Architecture tests using Pest's `arch()` to enforce: no `env()` outside `config/`, no `dd()`/`dump()` in app code.

**Non-Goals:**
- Vegapull binary integration (the fetch-and-import command).
- Seeding production data (production uses `cards:import` with real vegapull data).
- Changing factory definitions — they already produce realistic data.

## Decisions

### 1. Config-gated schedule via `import.schedule_enabled`

Add a boolean `schedule_enabled` key to `config/import.php`, defaulting to `false`. The schedule in `routes/console.php` checks this flag before registering the weekly command.

**Why:** Keeps the schedule opt-in per environment. Local dev and CI don't need a cron importing cards. Production enables it via `.env`. This follows the project convention of using `config()` over `env()`.

**Alternative considered:** Always registering the schedule and relying on the cron not being set up — rejected because it's implicit and error-prone.

### 2. DatabaseSeeder uses factories, not the import command

The seeder calls `Pack::factory()->has(Card::factory()->count(12))` to create a few packs with cards. It does not call `cards:import`.

**Why:** Factories are fast, deterministic, and don't require vegapull JSON fixtures on disk. The seeder is for local dev bootstrapping, not data accuracy.

### 3. Architecture tests in `tests/Architecture/`

Place arch tests in a dedicated `tests/Architecture/` directory (following Pest conventions) rather than mixing them into Feature or Unit.

**Why:** Architecture tests are cross-cutting constraints, not feature-specific. A dedicated directory makes them discoverable and aligns with Pest's `arch()` documentation examples.

### 4. Schedule runs `cards:import` without arguments

The scheduled command uses the default import path from `config('import.vegapull_path')`, same as running `cards:import` manually with no argument.

**Why:** The command already supports a configurable default path. No need to duplicate path logic in the schedule.

## Risks / Trade-offs

- **[Risk] Seeder data doesn't match real card structure** → Mitigation: Factories already use realistic domain values (categories, colors, etc.) from the CardFactory definition. Good enough for dev/testing.
- **[Risk] Schedule runs but vegapull data is stale** → Mitigation: The import is idempotent — running with unchanged data is a no-op. When vegapull integration lands later, the schedule will pick up fresh data automatically.
- **[Risk] `arch()` tests may flag existing violations** → Mitigation: Run tests after implementation to catch any current violations and fix them before committing.
