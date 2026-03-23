## MODIFIED Requirements

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
