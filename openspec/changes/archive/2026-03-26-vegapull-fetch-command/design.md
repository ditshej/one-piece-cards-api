## Context

The `cards:import` command handles JSON-to-database ingestion. The missing piece is automating the data acquisition step — running the `vega` CLI to scrape Bandai's card list. Currently this requires the user to manually run `vega pull all`, then copy JSON files to the right directory, then run import. This change collapses that into a single `cards:fetch` command.

The `vega` binary is a Rust CLI tool installed via `cargo install vegapull`. It outputs one JSON file per pack into a specified directory via `-o <path>`.

## Goals / Non-Goals

**Goals:**
- Single command to scrape and import: `php artisan cards:fetch`
- Clear error messages when `vega` binary is missing or fails
- Configurable binary path for different environments

**Non-Goals:**
- Installing or managing the `vega` binary itself
- Scheduled fetching (existing `cards:import` schedule is sufficient)
- Remote deployment or sync (separate change 8)

## Decisions

### 1. Use `Process` facade to execute vega binary

Run `vega pull all -o {path}` via Laravel's `Process` facade (`Illuminate\Support\Facades\Process`). This provides timeout handling, output capture, and exit code checking out of the box.

**Alternative:** `exec()` / `shell_exec()` — lower level, no timeout handling, less testable.

### 2. Output directly to `storage/vegapull/`

Use the existing `config('import.vegapull_path')` as the output directory for vega. This means `cards:import` can run immediately after without any file copying.

**Alternative:** Temp directory, then move — unnecessary complexity for no benefit.

### 3. Delegate import to existing `cards:import` via `Artisan::call()`

Call the existing import command rather than duplicating its logic. Single responsibility: `cards:fetch` handles acquisition, `cards:import` handles ingestion.

### 4. Config key for binary path

Add `import.vegapull_binary` (default: `vega`) to `config/import.php`. Allows users to specify an absolute path if `vega` is not in `$PATH`.

## Risks / Trade-offs

- **[vega not in PATH]** → Check binary existence with `Process::run('which vega')` before attempting the pull. Show actionable error message with install instructions.
- **[Scrape timeout]** → Set a generous timeout (5 minutes). Vega pulls all packs in parallel, but network issues could slow things down.
- **[Partial scrape]** → If vega fails mid-pull, some JSON files may exist. The import command handles this gracefully (imports whatever files are present).
