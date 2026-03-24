## MODIFIED Requirements

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

## ADDED Requirements

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
