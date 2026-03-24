## MODIFIED Requirements

### Requirement: Card searchability
Cards SHALL be searchable via API query parameters: color (`whereJsonContains`), category, cost, pack (`pack_id`), and free-text search on effect and trigger fields (`LIKE`).

#### Scenario: Filter cards by color
- **GIVEN** multiple cards with different colors exist
- **WHEN** `GET /api/v1/cards?color=Red` is requested
- **THEN** only cards containing "Red" in their colors array are returned
- **AND** the response status is 200

#### Scenario: Filter cards by category
- **GIVEN** cards with categories "Leader" and "Character" exist
- **WHEN** `GET /api/v1/cards?category=Leader` is requested
- **THEN** only Leader cards are returned
- **AND** the response status is 200

#### Scenario: Full-text search in effect and trigger text
- **GIVEN** cards with various effect and trigger texts exist
- **WHEN** `GET /api/v1/cards?search=draw` is requested
- **THEN** cards whose effect or trigger text contains "draw" are returned
- **AND** the response status is 200
