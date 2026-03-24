## Why

The API's core data layer, import pipeline, and endpoints are complete. What's missing is operational polish: a seeder for local development and testing, a scheduled import to keep production data current without manual intervention, and architecture tests to enforce project conventions as the codebase grows.

## What Changes

- **DatabaseSeeder**: Wire up Pack and Card factories so `php artisan db:seed` populates realistic sample data for local development.
- **Scheduled import**: Register a weekly `cards:import` schedule in `routes/console.php`, gated behind a config flag (`import.schedule_enabled`) so it's opt-in per environment.
- **Architecture tests**: Add Pest architecture tests enforcing that `env()` is never called outside `config/` and `dd()`/`dump()` are never used in application code.

## Non-goals

- Vegapull integration command (fetch + import) — deferred to a future change when vegapull binary distribution is resolved.
- Production deployment configuration (supervisor, cron setup docs).
- Changing any existing API endpoint behavior.

## Capabilities

### New Capabilities

_None — no new user-facing capabilities are introduced._

### Modified Capabilities

- `import`: Adding the scheduled import requirement implementation (config-gated weekly schedule) as specified in the existing spec.

## Impact

- **Affected code**: `database/seeders/DatabaseSeeder.php`, `routes/console.php`, `config/import.php`, new architecture test file(s).
- **APIs**: No endpoint changes.
- **Dependencies**: No new dependencies.
