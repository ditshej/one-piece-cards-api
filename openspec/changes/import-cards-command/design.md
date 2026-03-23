## Context

Pack and Card models exist with migrations and factories (Change 1). We need an Artisan command to populate the database from vegapull JSON files. Vegapull outputs one JSON file per set, each containing an array of card objects.

## Goals / Non-Goals

**Goals:**
- Import vegapull JSON files into the database
- Idempotent upserts (safe to re-run)
- Clear console output showing what was imported

**Non-Goals:**
- No vegapull binary execution (separate change)
- No JSON schema validation (trust vegapull output)
- No queue/async processing (sets are small enough for sync)

## Decisions

### 1. Use `updateOrCreate` for upserts

Each card has a unique `id` from vegapull. Use `Card::updateOrCreate(['id' => $id], $attributes)` for idempotent imports. Same for Pack.

**Why:** Simple, built-in Laravel method. No need for bulk upsert complexity at this data volume (~150 cards per set).

### 2. Default import path via config

Store the default path in `config/import.php` as `import.vegapull_path`, defaulting to `storage/vegapull`. The command accepts an optional `{path}` argument to override.

**Why:** Follows Laravel convention of keeping paths in config, not hardcoded. The `storage/` directory is the natural place for external data files.

### 3. One JSON file = one pack

Each vegapull JSON file contains cards from a single set. Extract `pack_id` and pack `name` from the JSON data to upsert the pack before its cards.

## Risks / Trade-offs

**[Vegapull JSON format changes]** → Low risk. Vegapull is a stable tool with a defined output format. If it changes, the import command will fail clearly on missing fields.
