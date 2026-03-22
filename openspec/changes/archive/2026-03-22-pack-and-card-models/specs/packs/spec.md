## ADDED Requirements

### Requirement: Pack uses string primary key
The Pack model SHALL use a non-incrementing string primary key (`id`), matching the vegapull pack identifiers (e.g., "OP01", "ST01").

#### Scenario: Create pack with string ID
- **WHEN** a Pack is created with id "OP01" and name "Romance Dawn"
- **THEN** the pack is persisted with the exact string id "OP01"
- **AND** no auto-incrementing integer id is generated

### Requirement: Pack factory for testing
The Pack model SHALL have a factory that generates realistic OPTCG pack data for use in tests.

#### Scenario: Generate pack from factory
- **WHEN** a Pack is created via its factory
- **THEN** the pack has a valid string id (e.g., "OP01" format)
- **AND** the pack has a non-empty name

## MODIFIED Requirements

### Requirement: Pack has many Cards
Each pack SHALL have a one-to-many relationship with cards. The relationship SHALL be defined as a `hasMany` Eloquent relationship on the Pack model, referencing the Card model via `pack_id`.

#### Scenario: Retrieve pack with cards
- **GIVEN** a pack "OP01" with 3 cards
- **WHEN** the pack is retrieved with its cards relationship
- **THEN** all 3 associated cards are returned
- **AND** each card has pack_id "OP01"
