## 1. Test Fixtures

- [ ] 1.1 Create a sample vegapull JSON fixture at tests/Fixtures/vegapull/OP01.json (3-5 cards matching real vegapull format)

## 2. Import Command

- [ ] 2.1 Write feature tests for cards:import (import creates packs and cards, idempotent upsert, update existing data, custom path argument, no files warning)
- [ ] 2.2 Create config/import.php with vegapull_path default
- [ ] 2.3 Create ImportCardsCommand (read JSON files, upsert packs and cards, display summary)

## 3. Verify

- [ ] 3.1 Run Pint to fix code style
- [ ] 3.2 Run full test suite with coverage and confirm all tests pass
