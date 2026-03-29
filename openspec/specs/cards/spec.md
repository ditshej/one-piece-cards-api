# Cards Specification

## Purpose
Management of OPTCG card data. Cards are the core domain entity of the API.
## Requirements
### Requirement: Card Model
The system SHALL store card data with the following attributes:
- `id` (string, unique) — Card identifier from vegapull (e.g., "OP01-001")
- `pack_id` (string) — Reference to the pack/set the card belongs to
- `name` (string) — Card name
- `rarity` (string) — Rarity level (C, UC, R, SR, SEC, L, P, etc.)
- `category` (string) — Card category (Leader, Character, Event, Stage)
- `colors` (array of strings) — Card colors (Red, Green, Blue, Purple, Black, Yellow)
- `cost` (integer, nullable) — Play cost
- `power` (integer, nullable) — Card power value
- `counter` (integer, nullable) — Counter value
- `attributes` (array of strings) — Card attributes (Strike, Ranged, Wisdom, Slash, etc.)
- `types` (array of strings) — Card types/traits (Straw Hat Crew, Fish-Man, etc.)
- `effect` (text, nullable) — Card effect text
- `trigger` (text, nullable) — Trigger effect text
- `img_url` (string) — URL to the card image on the official Bandai site

#### Scenario: Create card with all attributes
- **WHEN** a card is created with all required attributes
- **THEN** the card is persisted with the correct values for each field

### Requirement: Card belongs to Pack
Each card SHALL belong to exactly one pack. The relationship SHALL be defined as a `belongsTo` Eloquent relationship on the Card model, with a foreign key `pack_id` referencing the Pack model.

#### Scenario: Card with valid pack reference
- **GIVEN** a pack "OP01" exists
- **WHEN** a card is created with pack_id "OP01"
- **THEN** the associated pack is accessible via the `pack` relationship
- **AND** the pack's id is "OP01"

### Requirement: Card searchability
Cards SHALL be searchable via API query parameters: color (`whereJsonContains`), category, cost, pack (by pack **label**, resolved via a join on the `packs` table), and free-text search on effect and trigger fields (`LIKE`). All additional filter parameters defined in the `card-filtering` spec (name, rarity, attribute, type, cost range, power range, keyword, alt_art, per_page) are also supported on the `GET /api/v1/cards` endpoint.

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

### Requirement: Card uses string primary key
The Card model SHALL use a non-incrementing string primary key (`id`), matching the vegapull card identifiers (e.g., "OP01-001", "ST01-012").

#### Scenario: Create card with string ID
- **WHEN** a Card is created with id "OP01-001"
- **THEN** the card is persisted with the exact string id "OP01-001"
- **AND** no auto-incrementing integer id is generated

### Requirement: Card array fields stored as JSON
The Card model SHALL cast `colors`, `attributes`, and `types` fields to arrays, stored as JSON columns in the database.

#### Scenario: Card with multiple colors
- **GIVEN** a card with colors ["Red", "Green"]
- **WHEN** the card is retrieved
- **THEN** the colors field is a PHP array containing "Red" and "Green"

#### Scenario: Card with empty attributes
- **GIVEN** a card with an empty attributes array
- **WHEN** the card is retrieved
- **THEN** the attributes field is an empty PHP array

### Requirement: Card factory for testing
The Card model SHALL have a factory that generates realistic OPTCG card data for use in tests. The factory SHALL automatically create an associated Pack.

#### Scenario: Generate card from factory
- **WHEN** a Card is created via its factory
- **THEN** the card has all required attributes populated
- **AND** an associated pack exists in the database

#### Scenario: Generate card with specific category
- **WHEN** a Card is created via its factory with category "Leader"
- **THEN** the card has category "Leader"
- **AND** the card has a non-null power value

