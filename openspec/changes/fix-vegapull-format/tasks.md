## 1. Database

- [ ] 1.1 Add `label` column (nullable string) to packs migration
- [ ] 1.2 Update Pack model with `label` field
- [ ] 1.3 Update PackFactory to generate labels
- [ ] 1.4 Update PackResource to expose `label`

## 2. Test Fixtures

- [ ] 2.1 Replace test fixtures with real vegapull format (`packs.json` + `cards_*.json` in `json/` subdirectory)

## 3. Import Command

- [ ] 3.1 Update tests for `cards:import` to match new fixture format and pack metadata resolution
- [ ] 3.2 Rewrite `ImportCardsCommand` to read `packs.json` first, then `cards_*.json` files from `json/` subdirectory, map `img_full_url` to `img_url`

## 4. Fetch Command

- [ ] 4.1 Update tests for `cards:fetch` to use `pull packs` + `pull cards` instead of `pull all`
- [ ] 4.2 Rewrite `FetchCardsCommand` to run `vega pull packs` then `vega pull cards {id}` per pack, with `--language english`

## 5. Verification

- [ ] 5.1 Run full test suite (`php artisan test`)
- [ ] 5.2 Run Pint (`vendor/bin/pint --dirty`)
