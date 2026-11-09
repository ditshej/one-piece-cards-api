## MODIFIED Requirements

### Requirement: list-cards tool supports filtering
The MCP server SHALL expose a `list-cards` tool with the same filters as `GET /api/v1/cards`, with `pack_label` for `pack` and `limit` for `per_page`. List filters (`color`, `category`, `cost`, `power`, `counter`, `rarity`, `attribute`, `type`, `keyword`, `card_set` and their `*_not` variants) SHALL accept a single value or a list. `has_trigger`, `has_effect` and `has_counter` SHALL accept a boolean. `limit` SHALL be an integer from 1 to 100, default 20. All arguments SHALL be validated with the same rules as `CardsIndexRequest`. Filter logic SHALL live only in `Card::scopeApplyFilters()`.

#### Scenario: Invalid filter value
- **WHEN** an MCP client calls `list-cards` with a value of the wrong type or an unknown category or color
- **THEN** the tool returns an error and no cards
