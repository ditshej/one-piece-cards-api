## ADDED Requirements

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

## MODIFIED Requirements

### Requirement: Card belongs to Pack
Each card SHALL belong to exactly one pack. The relationship SHALL be defined as a `belongsTo` Eloquent relationship on the Card model, with a foreign key `pack_id` referencing the Pack model.

#### Scenario: Card with valid pack reference
- **GIVEN** a pack "OP01" exists
- **WHEN** a card is created with pack_id "OP01"
- **THEN** the associated pack is accessible via the `pack` relationship
- **AND** the pack's id is "OP01"
