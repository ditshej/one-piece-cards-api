## MODIFIED Requirements

### Requirement: List Cards Endpoint
The system SHALL provide `GET /api/v1/cards` returning a paginated list of cards.

#### Scenario: List all cards
- **GIVEN** cards exist in the database
- **WHEN** `GET /api/v1/cards` is requested
- **THEN** a paginated JSON response with card data is returned
- **AND** the response contains `data`, `links`, and `meta` keys
- **AND** the response status is 200

#### Scenario: List cards when none exist
- **WHEN** `GET /api/v1/cards` is requested
- **AND** no cards exist in the database
- **THEN** an empty `data` array is returned
- **AND** the response status is 200

#### Scenario: Filter cards by color
- **GIVEN** cards with various colors exist
- **WHEN** `GET /api/v1/cards?color=Red` is requested
- **THEN** only cards containing "Red" in their colors array are returned
- **AND** the response status is 200

#### Scenario: Filter cards by category
- **GIVEN** cards with various categories exist
- **WHEN** `GET /api/v1/cards?category=Leader` is requested
- **THEN** only Leader cards are returned
- **AND** the response status is 200

#### Scenario: Filter cards by cost
- **GIVEN** cards with various costs exist
- **WHEN** `GET /api/v1/cards?cost=5` is requested
- **THEN** only cards with cost 5 are returned
- **AND** the response status is 200

#### Scenario: Filter cards by pack
- **GIVEN** cards from multiple packs exist
- **WHEN** `GET /api/v1/cards?pack=OP15` is requested
- **THEN** only cards from pack OP15 are returned
- **AND** the response status is 200

#### Scenario: Search cards by effect text
- **GIVEN** cards with various effect texts exist
- **WHEN** `GET /api/v1/cards?search=draw` is requested
- **THEN** cards whose effect or trigger text contains "draw" are returned
- **AND** the response status is 200

#### Scenario: Combine multiple filters
- **GIVEN** cards with various attributes exist
- **WHEN** `GET /api/v1/cards?color=Red&cost=5` is requested
- **THEN** only cards matching both filters are returned
- **AND** the response status is 200

#### Scenario: Paginate card results
- **GIVEN** more cards exist than the default page size
- **WHEN** `GET /api/v1/cards?page=2` is requested
- **THEN** the second page of results is returned
- **AND** the response contains pagination metadata

### Requirement: Show Card Endpoint
The system SHALL provide `GET /api/v1/cards/{id}` returning a single card with full details.

#### Scenario: Retrieve existing card
- **GIVEN** a card with id "OP01-001" exists
- **WHEN** `GET /api/v1/cards/OP01-001` is requested
- **THEN** the full card data is returned via CardResource
- **AND** the response is wrapped in a `data` object
- **AND** the response status is 200

#### Scenario: Card not found
- **GIVEN** no card with id "INVALID-999" exists
- **WHEN** `GET /api/v1/cards/INVALID-999` is requested
- **THEN** the response status is 404
