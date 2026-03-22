# API Specification

## Purpose
REST API endpoints for consuming OPTCG card data. Designed for consumption by multiple external projects.

## Requirements

### Requirement: List Cards Endpoint
The system SHALL provide `GET /api/cards` returning a paginated list of cards.

#### Scenario: List all cards
- GIVEN cards exist in the database
- WHEN `GET /api/cards` is requested
- THEN a paginated JSON response with card data is returned
- AND the response status is 200

#### Scenario: Filter cards by color
- GIVEN cards with various colors exist
- WHEN `GET /api/cards?color=Red` is requested
- THEN only cards containing "Red" in their colors are returned
- AND the response status is 200

#### Scenario: Filter cards by category
- GIVEN cards with various categories exist
- WHEN `GET /api/cards?category=Leader` is requested
- THEN only Leader cards are returned
- AND the response status is 200

#### Scenario: Filter cards by cost
- GIVEN cards with various costs exist
- WHEN `GET /api/cards?cost=5` is requested
- THEN only cards with cost 5 are returned
- AND the response status is 200

#### Scenario: Filter cards by pack
- GIVEN cards from multiple packs exist
- WHEN `GET /api/cards?pack=OP15` is requested
- THEN only cards from pack OP15 are returned
- AND the response status is 200

#### Scenario: Search cards by effect text
- GIVEN cards with various effect texts exist
- WHEN `GET /api/cards?search=draw` is requested
- THEN cards whose effect or trigger text contains "draw" are returned
- AND the response status is 200

### Requirement: Show Card Endpoint
The system SHALL provide `GET /api/cards/{id}` returning a single card with full details.

#### Scenario: Retrieve existing card
- GIVEN a card with id "OP01-001" exists
- WHEN `GET /api/cards/OP01-001` is requested
- THEN the full card data including pack information is returned
- AND the response status is 200

#### Scenario: Card not found
- GIVEN no card with id "INVALID-999" exists
- WHEN `GET /api/cards/INVALID-999` is requested
- THEN a 404 error response is returned

### Requirement: List Packs Endpoint
The system SHALL provide `GET /api/packs` returning all available packs.

#### Scenario: List all packs
- GIVEN packs exist in the database
- WHEN `GET /api/packs` is requested
- THEN all packs are returned with their metadata
- AND the response status is 200

### Requirement: Show Pack Endpoint
The system SHALL provide `GET /api/packs/{id}` returning a single pack with its cards.

#### Scenario: Retrieve existing pack with cards
- GIVEN a pack "OP01" with cards exists
- WHEN `GET /api/packs/OP01` is requested
- THEN the pack data with all its cards is returned
- AND the response status is 200

#### Scenario: Pack not found
- GIVEN no pack with id "INVALID" exists
- WHEN `GET /api/packs/INVALID` is requested
- THEN a 404 error response is returned

### Requirement: JSON API Resources
The system SHALL use Laravel Eloquent API Resources for response formatting.

### Requirement: API Versioning
The API SHOULD support versioning (e.g., `/api/v1/cards`) to allow future breaking changes without affecting existing consumers.
