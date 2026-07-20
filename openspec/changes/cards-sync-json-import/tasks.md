## 1. Tests (TDD — write/update before implementing)

- [x] 1.1 Update `tests/Feature/Commands/SyncCardsCommandTest.php` to the new behaviour:
  - Keep `it('fails when sync config is missing')` — unchanged.
  - Add a `beforeEach`/helper that creates a fake local JSON dir (`storage/vegapull/json/` with `packs.json` + one `cards_*.json`), or fakes its existence, so precondition checks pass.
  - Replace `it('uploads the database via scp')` with `it('uploads the card json directory via scp')`: assert an `scp` run whose command contains the JSON path (`storage/vegapull/json`) **and** does NOT contain `database.sqlite`.
  - Add `it('never transfers the sqlite database file')` — regression guard: assert no faked process command contains `database.sqlite`.
  - Add `it('runs cards:import remotely over ssh')`: assert an `ssh` run whose command contains `artisan cards:import`.
  - Add `it('ensures the remote json directory exists')`: assert an `ssh`/`mkdir -p` run for the remote `storage/vegapull/json` path.
  - Update `it('fails when scp fails')` — still expects `SCP failed` / `assertFailed()`.
  - Add `it('fails when local json directory is missing or empty')`: no valid JSON → `assertFailed()`, and assert no `scp` ran.
  - Add `it('uses the configured php binary for remote artisan calls')`: set `config(['import.sync_php' => '/opt/php83/bin/php'])`, assert the `ssh` import/clear commands contain that path.
  - Keep `it('clears production cache after upload')` and `it('calls cards:fetch first when --fetch is passed')`.

## 2. Config

- [x] 2.1 Add `'sync_php' => env('SYNC_PHP', 'php')` to `config/import.php`.
- [x] 2.2 Add `SYNC_PHP=` (with a comment noting the prod value, e.g. `/opt/php83/bin/php`) to `.env.example`, near the other `SYNC_*` keys.
- [x] 2.3 Set `SYNC_PHP=/opt/php83/bin/php` in the local `.env` (next to the existing `SYNC_*` values) so the operator's sync targets the correct remote PHP immediately. Note: `cards:sync` runs locally, so this local value determines the remote PHP path.

## 3. Command Implementation

- [x] 3.1 Rewrite `app/Console/Commands/SyncCardsCommand.php` `handle()`:
  - Keep the config guard (`sync_host`/`sync_user`/`sync_path`) and the `--fetch` → `cards:fetch` step.
  - Resolve `$localJsonDir = config('import.vegapull_path').'/json'`; fail (`FAILURE`) before any remote action if it is missing or contains no `packs.json`/`cards_*.json`.
  - Read `$php = config('import.sync_php')` for the remote `artisan` calls (no hardcoded path).
  - `ssh … "mkdir -p {path}/storage/vegapull/json"` to ensure the remote target exists.
  - `scp -r -P {port} {localJsonDir}/. {user}@{host}:{path}/storage/vegapull/json/` (array-style `Process::run`); on failure show `SCP failed` and return `FAILURE`.
  - `ssh {user}@{host} -p {port} "cd {path} && {$php} artisan cards:import"`; on failure show an import-failed error and return `FAILURE`.
  - Keep the remote `optimize:clear` step (`{$php} artisan optimize:clear`, warn-only on failure) and the final `Sync complete.` / `SUCCESS`.
  - Do NOT reference `database.sqlite` anywhere in the command.

## 4. Spec Update

- [ ] 4.1 Apply the delta in `openspec/changes/cards-sync-json-import/specs/data-sync/spec.md` to `openspec/specs/data-sync/spec.md` at archive time (handled by `/opsx:archive`).

## 5. Cleanup & Verification

- [x] 5.1 Run `vendor/bin/pint --dirty --format agent`.
- [x] 5.2 Run `php artisan test --compact --filter=SyncCards` — all sync tests green.
- [x] 5.3 Sanity: `php artisan cards:sync --help` and confirm the command body no longer transfers `database.sqlite`.
