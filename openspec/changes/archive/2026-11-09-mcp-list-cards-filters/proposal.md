## Why

The MCP tool `list-cards` supports only a subset of the filters of `GET /api/v1/cards`, accepts only single values for list filters, and does not enforce its documented `limit` maximum of 100. REST and MCP are meant to mirror each other.

## What Changes

- `list-cards` gains `power`, `counter`, all `*_not` filters, `has_trigger`, `has_effect` and `has_counter`.
- List filters accept a single value or a list; single strings keep working.
- `limit` is validated to 1–100.
- A test compares the MCP schema keys with `CardsIndexRequest` rules so new REST filters without an MCP counterpart fail the suite.

## Capabilities

### New Capabilities

### Modified Capabilities

- `mcp`: the `list-cards` requirement covers the full REST filter set, list values and a validated `limit`.

## Impact

- `app/Mcp/Tools/ListCardsTool.php`, `tests/Feature/McpServerTest.php`.
- Filter logic stays in `Card::scopeApplyFilters()`. No REST change.
