## Why

The `cards:fetch` and `cards:import` commands were built against assumed vegapull JSON formats that don't match the actual output. Testing with the real `vega` binary revealed three issues:

1. `vega pull all` requires an interactive TTY — unusable via `Process::run()`
2. The card JSON uses numeric `pack_id` (e.g. `569101`) instead of labels (e.g. `OP01`), has no `pack_name`, uses `img_full_url` instead of `img_url`, and includes extra fields
3. Output goes to a `json/` subdirectory, not directly into the output path

## What Changes

- **`cards:fetch`**: Replace `vega pull all` with `vega pull packs` + `vega pull cards {id}` per pack (non-interactive)
- **`cards:import`**: Adapt to real vegapull JSON format — resolve pack name from `packs.json`, use `img_full_url`, handle numeric pack IDs
- **Test fixtures**: Replace with real vegapull output format
- **Pack model**: Use vegapull's numeric IDs as primary keys, store the label (e.g. `OP-01`) separately

## Non-goals

- Changing the API response format (CardResource/PackResource handle the mapping)
- Supporting multiple languages (English only for now)
- Image downloading (`--with-images` flag)

## Capabilities

### New Capabilities

None.

### Modified Capabilities

- `import`: Adapting Import Command and Vegapull Integration to handle the real vegapull JSON format and non-interactive fetching

## Impact

- **Models:** Pack gets a `label` field (migration) for the human-readable ID like `OP-01`
- **Import command:** Reads `packs.json` for pack metadata, maps new card fields
- **Fetch command:** Different process invocations (packs + cards loop instead of `pull all`)
- **Test fixtures:** Updated to match real vegapull output
- **API endpoints:** PackResource/CardResource may need to expose `label`
