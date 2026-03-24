## MODIFIED Requirements

### Requirement: List Packs Endpoint
The system SHALL provide `GET /api/v1/packs` returning all available packs.

#### Scenario: List all packs
- **GIVEN** packs exist in the database
- **WHEN** `GET /api/v1/packs` is requested
- **THEN** all packs are returned with their id and name
- **AND** the response status is 200
- **AND** the response is wrapped in a `data` array

#### Scenario: List packs when none exist
- **WHEN** `GET /api/v1/packs` is requested
- **AND** no packs exist in the database
- **THEN** an empty `data` array is returned
- **AND** the response status is 200

### Requirement: Show Pack Endpoint
The system SHALL provide `GET /api/v1/packs/{id}` returning a single pack with its cards.

#### Scenario: Retrieve existing pack with cards
- **GIVEN** a pack "OP01" with 3 cards exists
- **WHEN** `GET /api/v1/packs/OP01` is requested
- **THEN** the pack data is returned with id, name, and a nested `cards` array
- **AND** each card in the array includes all card fields formatted through CardResource
- **AND** the response is wrapped in a `data` object
- **AND** the response status is 200

#### Scenario: Pack not found
- **GIVEN** no pack with id "INVALID" exists
- **WHEN** `GET /api/v1/packs/INVALID` is requested
- **THEN** a JSON error response is returned with a `message` field
- **AND** the response status is 404

### Requirement: JSON API Resources
The system SHALL use Laravel Eloquent API Resources for response formatting.

#### Scenario: Pack response uses PackResource
- **WHEN** a pack is returned from the API
- **THEN** the response is formatted through a PackResource class
- **AND** the response contains `id` and `name` fields

#### Scenario: Card response uses CardResource
- **WHEN** a card is returned as part of a pack show response
- **THEN** the card is formatted through a CardResource class
- **AND** the response contains all card fields: id, pack_id, name, rarity, category, colors, cost, power, counter, attributes, types, effect, trigger, img_url

### Requirement: API Versioning
The system SHALL serve all API routes under the `/api/v1` prefix to allow future breaking changes without affecting existing consumers.

#### Scenario: API routes are versioned
- **WHEN** a request is made to `/api/v1/packs`
- **THEN** the versioned endpoint responds successfully
- **AND** the response status is 200

#### Scenario: Unversioned API path returns 404
- **WHEN** a request is made to `/api/packs` (without version prefix)
- **THEN** the response status is 404
