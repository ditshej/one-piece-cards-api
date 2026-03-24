## 1. Database Seeder

- [x] 1.1 Write feature test for DatabaseSeeder (seeds packs with associated cards, completes without errors)
- [ ] 1.2 Update `DatabaseSeeder` to create sample packs with cards using Pack and Card factories

## 2. Scheduled Import

- [ ] 2.1 Add `schedule_enabled` key (default `false`) to `config/import.php` with corresponding `IMPORT_SCHEDULE_ENABLED` env var
- [ ] 2.2 Write feature test for the scheduled import (command is registered weekly when enabled, not registered when disabled)
- [ ] 2.3 Register weekly `cards:import` schedule in `routes/console.php`, gated by `config('import.schedule_enabled')`

## 3. Architecture Tests

- [ ] 3.1 Create `tests/Architecture/ArchitectureTest.php` with Pest arch tests: no `env()` outside `config/`, no `dd()`/`dump()` in app code
- [ ] 3.2 Run full test suite and fix any violations

## 4. Finalize

- [ ] 4.1 Run Pint and verify all tests pass
