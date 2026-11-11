## Why

The MCP tool `list-cards` validates only `limit`. All other values reach `Card::scopeApplyFilters()` unchecked, so `has_trigger: "false"` counts as true and an unknown category or color gives no clear error. REST validates all of them with `CardsIndexRequest`.

## What Changes

- `list-cards` validates its arguments with the same rules as `CardsIndexRequest` (one source), mapping `pack_label` to `pack` and `limit` to `per_page`.
- Invalid values (wrong type, unknown category or color) return a tool error.
- `color` and `color_not` only accept the six known colors, in REST and MCP.

## Capabilities

### New Capabilities

### Modified Capabilities

- `mcp`: the `list-cards` requirement covers validation of all filter values.

## Impact

- `app/Http/Requests/CardsIndexRequest.php`, `app/Mcp/Tools/ListCardsTool.php`, `tests/Feature/McpServerTest.php`, REST color test.
- Filter logic stays in `Card::scopeApplyFilters()`.
- REST: unknown colors now return 422 instead of an empty result.
