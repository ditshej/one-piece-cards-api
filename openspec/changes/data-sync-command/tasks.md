## 1. Config

- [x] 1.1 Add `sync_host`, `sync_user`, `sync_port`, `sync_path` keys to `config/import.php`
- [x] 1.2 Add `SYNC_HOST`, `SYNC_USER`, `SYNC_PORT`, `SYNC_PATH` to `.env.example` with placeholder values
- [x] 1.3 Add the same keys to `.env` with real Metanet values

## 2. Command

- [x] 2.1 Create `app/Console/Commands/SyncCardsCommand.php` with signature `cards:sync` and `--fetch` option
- [x] 2.2 Implement config validation — abort with error if required keys are missing
- [x] 2.3 Implement `--fetch` flag: call `cards:fetch` via `$this->call()` before syncing
- [x] 2.4 Implement SCP upload of `database/database.sqlite` to remote path
- [x] 2.5 Implement SSH cache clear: run `artisan optimize:clear` on the server after upload

## 3. Tests

- [x] 3.1 Feature test: `cards:sync` fails with missing config
- [x] 3.2 Feature test: `cards:sync` runs SCP and SSH with correct arguments
- [x] 3.3 Feature test: `cards:sync --fetch` calls `cards:fetch` first

## 4. Verification

- [ ] 4.1 Run `php artisan cards:sync` locally — verify DB uploaded and cache cleared on server
- [ ] 4.2 Verify `https://op-cards.ditshej.ch/api/v1/cards` returns updated data
