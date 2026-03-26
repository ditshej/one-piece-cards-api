## Why

The `cards:import` command exists but requires manually placing vegapull JSON files in `storage/vegapull/`. This means running `vega pull all` separately, then importing — two steps that should be one. The import spec already defines a "Vegapull Integration" requirement that was deferred during initial implementation.

## What Changes

- New Artisan command `cards:fetch` that executes the `vega` binary and pipes output directly into the existing import flow
- New config key `import.vegapull_binary` for the path to the vega binary (default: `vega`)
- Extended scenarios in the `import` spec to cover error cases (binary not found, scrape failure)

## Non-goals

- No scheduled fetch (the existing scheduled import via `cards:import` is sufficient)
- No automatic deployment or sync to remote servers (separate change)
- No Docker/container setup for vegapull

## Capabilities

### New Capabilities

None — this implements an already-defined requirement.

### Modified Capabilities

- `import`: Implementing the deferred "Vegapull Integration" requirement with additional error-handling scenarios

## Impact

- **Code:** New command class, config addition
- **Dependencies:** Requires `vega` binary installed on the system (external, not a PHP dependency)
- **API endpoints:** None affected — this is a CLI-only change
