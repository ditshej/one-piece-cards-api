# Import Specification

## Purpose
Importing card data from vegapull JSON files into the database. This is the primary mechanism for keeping the API data current.
## Requirements
### Requirement: Import Command
The system SHALL provide an Artisan command `cards:import {path?}` that reads vegapull JSON files and upserts card and pack data into the database. The `path` argument defaults to a configurable directory. The command SHALL read all `.json` files from the given path, extract the pack from the first card's `pack_id`, upsert the pack, then upsert all cards using `updateOrCreate`.

#### Scenario: Import a single set
- **GIVEN** a vegapull JSON file for set OP01 exists at the import path
- **WHEN** `php artisan cards:import` is executed
- **THEN** all cards from the JSON file are created in the database
- **AND** the associated pack "OP01" is created
- **AND** a summary showing the number of imported cards is displayed

#### Scenario: Import is idempotent
- **GIVEN** cards from OP01 already exist in the database
- **WHEN** `php artisan cards:import` is executed again with the same data
- **THEN** existing cards are updated (not duplicated)
- **AND** the total card count remains the same

#### Scenario: Import with updated data
- **GIVEN** a card "OP01-001" exists with power 5000
- **WHEN** `php artisan cards:import` is executed with a JSON file where "OP01-001" has power 6000
- **THEN** the card's power is updated to 6000

#### Scenario: Import with custom path
- **GIVEN** vegapull JSON files exist at `/tmp/vegapull-data`
- **WHEN** `php artisan cards:import /tmp/vegapull-data` is executed
- **THEN** files from that directory are imported

#### Scenario: Import with no JSON files
- **GIVEN** the import path contains no `.json` files
- **WHEN** `php artisan cards:import` is executed
- **THEN** a warning message is displayed
- **AND** no database changes are made

### Requirement: Vegapull Integration
The system SHALL provide an optional Artisan command that executes vegapull to fetch fresh JSON data before importing.

#### Scenario: Fetch and import new sets
- GIVEN vegapull is installed on the system
- WHEN the fetch-and-import command is executed
- THEN vegapull scrapes the latest card data from Bandai
- AND the scraped JSON files are imported into the database

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
