## ADDED Requirements

### Requirement: cards:sync uploads local SQLite DB to production
The system SHALL provide an Artisan command `cards:sync` that uploads `database/database.sqlite` to the configured production server via SCP.

#### Scenario: Successful sync
- **WHEN** `php artisan cards:sync` is run with valid SSH config
- **THEN** the local SQLite DB is uploaded to the server and the production cache is cleared

#### Scenario: Missing SSH config
- **WHEN** required config keys (`sync_host`, `sync_user`, `sync_path`) are missing or empty
- **THEN** the command exits with an error message before attempting SCP

### Requirement: --fetch flag runs cards:fetch before syncing
The command SHALL accept a `--fetch` flag that runs `cards:fetch` before uploading the DB.

#### Scenario: Sync with fetch
- **WHEN** `php artisan cards:sync --fetch` is run
- **THEN** `cards:fetch` runs first, then the DB is uploaded to production

### Requirement: Sync credentials are config-driven
SSH connection details (host, user, port, remote path) SHALL be read from `config/import.php` via environment variables, never hardcoded.

#### Scenario: Config from .env
- **WHEN** `SYNC_HOST`, `SYNC_USER`, `SYNC_PORT`, `SYNC_PATH` are set in `.env`
- **THEN** the command uses these values for the SCP and SSH commands
