## Why

The database has models and migrations but no way to populate it with real card data. The `cards:import` command is needed to ingest vegapull JSON files, which is the primary data source for the API. Without it, the API has no data to serve.

## What Changes

- Create Artisan command `cards:import {path?}` that reads vegapull JSON files from a directory
- Upsert packs and cards into the database (create or update, never duplicate)
- Display a summary of imported/updated counts after each run
- Create test fixture JSON files matching the vegapull format

## Capabilities

### New Capabilities

_(none)_

### Modified Capabilities

- `import`: Implementing the Import Command requirement (command signature, JSON parsing, upsert logic, summary output)

## Impact

- **Console:** New Artisan command `cards:import`
- **Database:** Populates packs and cards tables via upsert
- **No API impact** — this is a CLI-only command
- **No affected API endpoints**

### Non-goals

- No vegapull binary integration (fetching fresh data) — that's a separate change
- No scheduled/cron import — that's a separate change
- No validation of JSON schema — trust vegapull output format
