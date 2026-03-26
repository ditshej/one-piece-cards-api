## 1. Database

- [x] 1.1 Add `label` column (nullable string) to packs migration
- [x] 1.2 Update Pack model with `label` field
- [x] 1.3 Update PackFactory to generate labels
- [x] 1.4 Update PackResource to expose `label`

## 2. Test Fixtures

- [x] 2.1 Replace test fixtures with real vegapull format (`packs.json` + `cards_*.json` in `json/` subdirectory)

## 3. Import Command

- [x] 3.1 Update tests for `cards:import` to match new fixture format and pack metadata resolution
- [x] 3.2 Rewrite `ImportCardsCommand` to read `packs.json` first, then `cards_*.json` files from `json/` subdirectory, map `img_full_url` to `img_url`

## 4. Fetch Command

- [x] 4.1 Update tests for `cards:fetch` to use `pull packs` + `pull cards` instead of `pull all`
- [x] 4.2 Rewrite `FetchCardsCommand` to run `vega pull packs` then `vega pull cards {id}` per pack, with `--language english`

## 5. Verification

- [x] 5.1 Run full test suite (`php artisan test`)
- [x] 5.2 Run Pint (`vendor/bin/pint --dirty`)
