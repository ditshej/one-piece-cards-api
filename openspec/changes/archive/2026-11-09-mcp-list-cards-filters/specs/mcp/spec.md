## MODIFIED Requirements

### Requirement: list-cards tool supports filtering
The MCP server SHALL expose a `list-cards` tool with the same filters as `GET /api/v1/cards`, with `pack_label` for `pack` and `limit` for `per_page`. List filters (`color`, `category`, `cost`, `power`, `counter`, `rarity`, `attribute`, `type`, `keyword`, `card_set` and their `*_not` variants) SHALL accept a single value or a list. `has_trigger`, `has_effect` and `has_counter` SHALL accept a boolean. `limit` SHALL be an integer from 1 to 100, default 20. Filter logic SHALL live only in `Card::scopeApplyFilters()`.

#### Scenario: Cards filtered by color
- **WHEN** an MCP client calls `list-cards` with `color` parameter
- **THEN** only cards containing that color are returned

#### Scenario: Cards filtered by a list of colors
- **WHEN** an MCP client calls `list-cards` with `color` as a list
- **THEN** cards containing any of the colors are returned

#### Scenario: Cards filtered by counter and exclusion
- **WHEN** an MCP client calls `list-cards` with `counter` or a `*_not` filter
- **THEN** only matching cards are returned

#### Scenario: Cards filtered by presence of trigger, effect or counter
- **WHEN** an MCP client calls `list-cards` with `has_trigger`, `has_effect` or `has_counter`
- **THEN** only cards with (true) or without (false) that field are returned

#### Scenario: Cards searched by text
- **WHEN** an MCP client calls `list-cards` with `search` parameter
- **THEN** only cards whose effect or trigger contains the search term are returned

#### Scenario: Limit out of range
- **WHEN** an MCP client calls `list-cards` with `limit` below 1 or above 100
- **THEN** the tool returns an error and no cards
