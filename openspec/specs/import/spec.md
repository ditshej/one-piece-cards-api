# Import Specification

## Purpose
Importing card data from vegapull JSON files into the database. This is the primary mechanism for keeping the API data current.

## Requirements

### Requirement: Import Command
The system SHALL provide an Artisan command `cards:import` that reads vegapull JSON files and upserts card and pack data into the database.

#### Scenario: Import a single set
- GIVEN a vegapull JSON file for set OP01 exists
- WHEN `php artisan cards:import` is executed
- THEN all cards from the JSON file are created or updated in the database
- AND the associated pack is created if it doesn't exist
- AND a summary of imported/updated cards is displayed

#### Scenario: Import is idempotent
- GIVEN cards from OP01 already exist in the database
- WHEN `php artisan cards:import` is executed again with the same data
- THEN existing cards are updated (not duplicated)
- AND the card count remains the same

### Requirement: Vegapull Integration
The system SHOULD provide an optional Artisan command that executes vegapull to fetch fresh JSON data before importing.

#### Scenario: Fetch and import new sets
- GIVEN vegapull is installed on the system
- WHEN the fetch-and-import command is executed
- THEN vegapull scrapes the latest card data from Bandai
- AND the scraped JSON files are imported into the database

### Requirement: Scheduled Import
The system MAY support a scheduled command that periodically runs vegapull and imports new data.

#### Scenario: Scheduled weekly import
- GIVEN the scheduled import is configured
- WHEN the schedule runs
- THEN vegapull fetches the latest data
- AND new or updated cards are imported
