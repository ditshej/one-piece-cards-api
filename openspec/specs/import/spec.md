# Import Specification

## Purpose
Importing card data from vegapull JSON files into the database. This is the primary mechanism for keeping the API data current.
## Requirements
### Requirement: Import Command
The system SHALL provide an Artisan command `cards:import {path?}` that reads vegapull JSON files and upserts card and pack data into the database. The `path` argument defaults to a configurable directory. The command SHALL first read `packs.json` from the `json/` subdirectory to resolve pack metadata (name, label), then read all `cards_*.json` files and upsert cards. Cards reference packs by vegapull's numeric pack ID. The command SHALL use `img_full_url` for the card image URL.

#### Scenario: Import a single set
- **GIVEN** vegapull JSON files for set OP-01 (pack ID `569101`) exist at the import path
- **WHEN** `php artisan cards:import` is executed
- **THEN** all cards from the JSON file are created in the database
- **AND** the associated pack is created with its name and label from `packs.json`
- **AND** a summary showing the number of imported cards is displayed

#### Scenario: Import is idempotent
- **GIVEN** cards from OP-01 already exist in the database
- **WHEN** `php artisan cards:import` is executed again with the same data
- **THEN** existing cards are updated (not duplicated)
- **AND** the total card count remains the same

#### Scenario: Import with updated data
- **GIVEN** a card "OP01-001" exists with power 5000
- **WHEN** `php artisan cards:import` is executed with a JSON file where "OP01-001" has power 6000
- **THEN** the card's power is updated to 6000

#### Scenario: Import with custom path
- **GIVEN** vegapull JSON files exist at `/tmp/vegapull-data/json/`
- **WHEN** `php artisan cards:import /tmp/vegapull-data` is executed
- **THEN** files from that directory's `json/` subdirectory are imported

#### Scenario: Import with no JSON files
- **GIVEN** the import path contains no card JSON files
- **WHEN** `php artisan cards:import` is executed
- **THEN** a warning message is displayed
- **AND** no database changes are made

#### Scenario: Import resolves pack metadata from packs.json
- **GIVEN** a `packs.json` exists with pack `569101` having label `OP-01` and title `ROMANCE DAWN`
- **WHEN** `php artisan cards:import` is executed
- **THEN** the pack is created with name `ROMANCE DAWN` and label `OP-01`

### Requirement: Vegapull Integration
The system SHALL provide an Artisan command `cards:fetch` that executes the vegapull binary to scrape fresh card data from Bandai's official card list and imports the results into the database. The command SHALL run `vega pull packs` to fetch pack metadata, then `vega pull cards {id}` for each pack to fetch card data. All vegapull commands SHALL pass `--language english` to avoid interactive prompts. After scraping, the command SHALL call `cards:import` to ingest the data.

#### Scenario: Fetch and import new sets
- **GIVEN** vegapull (`vega`) is installed and available in PATH
- **WHEN** `php artisan cards:fetch` is executed
- **THEN** vegapull fetches pack list and card data for all packs from Bandai
- **AND** the scraped JSON files are saved to the configured vegapull path
- **AND** `cards:import` is called to import the data into the database
- **AND** a summary of imported cards is displayed

#### Scenario: Vegapull binary not found
- **GIVEN** the `vega` binary is not installed or not in PATH
- **WHEN** `php artisan cards:fetch` is executed
- **THEN** an error message is displayed indicating the binary was not found
- **AND** the command exits with a failure exit code
- **AND** no import is attempted

#### Scenario: Vegapull scrape fails
- **GIVEN** vegapull is installed but the scrape fails (network error, site unavailable)
- **WHEN** `php artisan cards:fetch` is executed
- **THEN** an error message is displayed with the failure details
- **AND** the command exits with a failure exit code
- **AND** no import is attempted

#### Scenario: Fetch with custom binary path
- **GIVEN** `config('import.vegapull_binary')` is set to `/usr/local/bin/vega`
- **WHEN** `php artisan cards:fetch` is executed
- **THEN** the command uses `/usr/local/bin/vega` to execute the scrape

### Requirement: Scheduled Import
The system SHALL support a config-gated scheduled command that periodically runs `cards:import` to import card data. The schedule SHALL be controlled by the `import.schedule_enabled` configuration value, defaulting to `false`. When enabled, the system SHALL run `cards:import` weekly using the default import path.

#### Scenario: Scheduled weekly import when enabled
- **GIVEN** `config('import.schedule_enabled')` is `true`
- **WHEN** the Laravel scheduler runs
- **THEN** the `cards:import` command is executed weekly
- **AND** new or updated cards are imported from the default vegapull path

#### Scenario: Schedule is disabled by default
- **GIVEN** `config('import.schedule_enabled')` is not explicitly set
- **WHEN** the application boots
- **THEN** no scheduled import command is registered

#### Scenario: Schedule respects environment configuration
- **GIVEN** `IMPORT_SCHEDULE_ENABLED=true` is set in the environment
- **WHEN** `config('import.schedule_enabled')` is resolved
- **THEN** the value is `true`

### Requirement: Database Seeder
The DatabaseSeeder SHALL create sample packs with associated cards using the existing Pack and Card factories. This provides realistic development data without requiring vegapull JSON files.

#### Scenario: Seed creates packs with cards
- **WHEN** `php artisan db:seed` is executed
- **THEN** multiple packs are created in the database
- **AND** each pack has associated cards
- **AND** all card attributes are populated with factory-generated values

#### Scenario: Seed is idempotent with fresh database
- **GIVEN** a freshly migrated database
- **WHEN** `php artisan db:seed` is executed
- **THEN** the seeder completes without errors

### Requirement: Architecture Tests
The system SHALL include Pest architecture tests that enforce project coding conventions. These tests SHALL verify that `env()` is not called outside of `config/` files and that `dd()`/`dump()` are not used in application code.

#### Scenario: No env() outside config
- **GIVEN** the application codebase
- **WHEN** architecture tests are run
- **THEN** no file outside `config/` calls the `env()` function

#### Scenario: No dd() or dump() in application code
- **GIVEN** the application codebase
- **WHEN** architecture tests are run
- **THEN** no application code contains `dd()` or `dump()` calls
