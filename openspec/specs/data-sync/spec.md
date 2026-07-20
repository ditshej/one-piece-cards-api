## Requirements

### Requirement: cards:sync uploads card JSON to production and imports remotely
The system SHALL provide an Artisan command `cards:sync` that uploads the local vegapull card JSON files (`storage/vegapull/json/`, containing `packs.json` and `cards_*.json`) to the configured production server via SCP, then runs `php artisan cards:import` on the server via SSH. The command SHALL NOT transfer `database/database.sqlite` or any database file, so that production auth data (`users`, `sessions`, `personal_access_tokens`) is never overwritten by a sync.

#### Scenario: Successful sync
- **WHEN** `php artisan cards:sync` is run with valid SSH config and local card JSON present
- **THEN** the card JSON directory is uploaded to the server
- **AND** `php artisan cards:import` is run on the server to upsert the card data
- **AND** the production cache is cleared
- **AND** no SQLite database file is transferred

#### Scenario: Production auth data survives a sync
- **GIVEN** a personal access token exists on production
- **WHEN** `php artisan cards:sync` is run
- **THEN** only card data is updated on production
- **AND** the production `personal_access_tokens`, `users`, and `sessions` remain unchanged

#### Scenario: Remote JSON directory is created if missing
- **WHEN** `php artisan cards:sync` runs and the remote `storage/vegapull/json` directory does not exist
- **THEN** the command creates it before uploading

#### Scenario: Missing local card JSON
- **WHEN** the local vegapull JSON directory is missing or contains no `packs.json`/`cards_*.json`
- **THEN** the command exits with an error before attempting any SCP or SSH

#### Scenario: Missing SSH config
- **WHEN** required config keys (`sync_host`, `sync_user`, `sync_path`) are missing or empty
- **THEN** the command exits with an error message before attempting SCP

### Requirement: --fetch flag runs cards:fetch before syncing
The command SHALL accept a `--fetch` flag that runs `cards:fetch` locally before uploading the card JSON.

#### Scenario: Sync with fetch
- **WHEN** `php artisan cards:sync --fetch` is run
- **THEN** `cards:fetch` runs first to refresh the local JSON, then the card JSON is uploaded to production and imported

### Requirement: Sync credentials and remote PHP binary are config-driven
SSH connection details (host, user, port, remote path) and the remote PHP binary SHALL be read from `config/import.php` via environment variables, never hardcoded. The remote PHP binary SHALL default to `php` and be overridable via `SYNC_PHP`.

#### Scenario: Config from .env
- **WHEN** `SYNC_HOST`, `SYNC_USER`, `SYNC_PORT`, `SYNC_PATH` are set in `.env`
- **THEN** the command uses these values for the SCP and SSH commands

#### Scenario: Configurable remote PHP binary
- **WHEN** `SYNC_PHP` is set to `/opt/php83/bin/php` in `.env`
- **THEN** the remote `artisan cards:import` and `artisan optimize:clear` calls are executed with `/opt/php83/bin/php`

#### Scenario: Default PHP binary
- **WHEN** `SYNC_PHP` is not set
- **THEN** the remote `artisan` calls use `php`
