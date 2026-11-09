## Requirements

### Requirement: list-packs tool returns all packs
The MCP server SHALL expose a `list-packs` tool that returns all packs with their `id`, `name`, and `label`.

#### Scenario: All packs returned
- **WHEN** an MCP client calls `list-packs`
- **THEN** the tool returns an array of all packs ordered by ID

### Requirement: get-pack tool returns a pack with its cards
The MCP server SHALL expose a `get-pack` tool that accepts a `pack_id` parameter and returns the pack with all its cards.

#### Scenario: Existing pack returned with cards
- **WHEN** an MCP client calls `get-pack` with a valid `pack_id`
- **THEN** the tool returns the pack including a `cards` array with all card fields

#### Scenario: Unknown pack returns error
- **WHEN** an MCP client calls `get-pack` with an unknown `pack_id`
- **THEN** the tool returns an error indicating the pack was not found

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

### Requirement: get-card tool returns a single card
The MCP server SHALL expose a `get-card` tool that accepts a `card_id` parameter and returns all fields of that card.

#### Scenario: Existing card returned
- **WHEN** an MCP client calls `get-card` with a valid `card_id`
- **THEN** the tool returns all card fields including colors, attributes, types, effect, and trigger

#### Scenario: Unknown card returns error
- **WHEN** an MCP client calls `get-card` with an unknown `card_id`
- **THEN** the tool returns an error indicating the card was not found
