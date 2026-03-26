## MODIFIED Requirements

### Requirement: Vegapull Integration
The system SHALL provide an Artisan command `cards:fetch` that executes the vegapull binary to scrape fresh card data from Bandai's official card list and imports the results into the database. The command SHALL use the binary path from `config('import.vegapull_binary')` (default: `vega`) and output JSON files to `config('import.vegapull_path')`. After scraping, the command SHALL call `cards:import` to ingest the data.

#### Scenario: Fetch and import new sets
- **GIVEN** vegapull (`vega`) is installed and available in PATH
- **WHEN** `php artisan cards:fetch` is executed
- **THEN** vegapull scrapes the latest card data from Bandai
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
