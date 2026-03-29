# Card Set Field Specification

## Purpose
Derived `card_set` and `alt_art_variant` columns on the Card model, enabling efficient set-based filtering and alt art detection without string parsing at query time.

## Requirements

### Requirement: card_set column
The Card model SHALL have a `card_set` column (string, nullable) that stores the set prefix derived from the card `id` (e.g., "OP01" from "OP01-001", "ST01" from "ST01-012").

#### Scenario: card_set populated on create for standard card
- **GIVEN** a card with id "OP01-001" is saved
- **THEN** `card_set` is "OP01"

#### Scenario: card_set populated on create for starter deck card
- **GIVEN** a card with id "ST01-012" is saved
- **THEN** `card_set` is "ST01"

#### Scenario: card_set populated for alt art card
- **GIVEN** a card with id "OP13-113_p1" is saved
- **THEN** `card_set` is "OP13"

---

### Requirement: alt_art_variant column
The Card model SHALL have an `alt_art_variant` column (string, nullable) that stores the variant suffix extracted from the card `id` after the `_` separator (e.g., "p1" from "OP13-113_p1"). Non-alt-art cards SHALL have a null value.

#### Scenario: alt_art_variant is null for standard card
- **GIVEN** a card with id "OP01-001" is saved
- **THEN** `alt_art_variant` is null

#### Scenario: alt_art_variant extracted for alt art card
- **GIVEN** a card with id "OP13-113_p1" is saved
- **THEN** `alt_art_variant` is "p1"

#### Scenario: alt_art_variant handles multiple variants
- **GIVEN** a card with id "OP13-113_p2" is saved
- **THEN** `alt_art_variant` is "p2"

---

### Requirement: Automatic derivation on save
Both `card_set` and `alt_art_variant` SHALL be derived automatically from the card `id` when a card is created or updated, with no manual input required.

#### Scenario: Values are set automatically without explicit assignment
- **WHEN** a Card is created via factory with id "OP05-020"
- **THEN** `card_set` is "OP05" and `alt_art_variant` is null
- **AND** no explicit assignment of `card_set` or `alt_art_variant` was made

---

### Requirement: Database columns indexed
The `card_set` column SHALL be indexed in the database to support efficient filtering queries.

#### Scenario: Filtering by card_set uses index
- **GIVEN** a large number of cards from multiple sets exist
- **WHEN** `GET /api/v1/cards?card_set=OP01` is called
- **THEN** only cards from "OP01" are returned with status 200
