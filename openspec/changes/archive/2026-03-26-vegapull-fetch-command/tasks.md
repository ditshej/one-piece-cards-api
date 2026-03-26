## 1. Configuration

- [x] 1.1 Add `vegapull_binary` key to `config/import.php` (default: `vega`)

## 2. Tests

- [x] 2.1 Write feature tests for `cards:fetch` command: binary not found, scrape failure, successful fetch-and-import
- [x] 2.2 Ensure tests mock the `Process` facade (no actual vega execution in tests)

## 3. Command Implementation

- [x] 3.1 Create `FetchCardsCommand` with signature `cards:fetch`, using `Process` facade to run `vega pull all -o {vegapull_path}`
- [x] 3.2 Add binary existence check with actionable error message
- [x] 3.3 Delegate to `cards:import` via `Artisan::call()` after successful scrape
- [x] 3.4 Display summary output

## 4. Verification

- [x] 4.1 Run full test suite (`php artisan test`)
- [x] 4.2 Run Pint (`vendor/bin/pint --dirty`)
