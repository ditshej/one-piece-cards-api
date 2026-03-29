## ADDED Requirements

### Requirement: card_set field
The system SHALL store the origin set prefix of each card as `card_set` (VARCHAR, nullable). The value SHALL be derived from the portion of the card ID before the first `-` character (e.g., `OP03` from `OP03-072`). It SHALL be computed at import time and backfilled for existing cards via migration.

#### Scenario: card_set derived from standard ID
- **WHEN** a card with id `OP03-072` is imported
- **THEN** `card_set` is stored as `OP03`

#### Scenario: card_set derived from alt art ID
- **WHEN** a card with id `OP03-072_p1` is imported
- **THEN** `card_set` is stored as `OP03`

---

### Requirement: alt_art_variant field
The system SHALL store the alt art variant number as `alt_art_variant` (INTEGER, nullable). The value SHALL be extracted from the `_p{n}` suffix in the card ID. Non-alt-art cards SHALL have `null`.

#### Scenario: alt_art_variant for standard card
- **WHEN** a card with id `OP13-113` is imported
- **THEN** `alt_art_variant` is `null`

#### Scenario: alt_art_variant for first variant
- **WHEN** a card with id `OP13-113_p1` is imported
- **THEN** `alt_art_variant` is `1`

#### Scenario: alt_art_variant for second variant
- **WHEN** a card with id `OP13-113_p2` is imported
- **THEN** `alt_art_variant` is `2`

---

### Requirement: Both fields exposed in API response
The card API response SHALL include `card_set` and `alt_art_variant` for every card.

#### Scenario: Standard card response includes both fields
- **WHEN** `GET /api/v1/cards/OP13-113` is called
- **THEN** the response includes `card_set: "OP13"` and `alt_art_variant: null`

#### Scenario: Alt art card response includes variant number
- **WHEN** `GET /api/v1/cards/OP13-113_p2` is called
- **THEN** the response includes `card_set: "OP13"` and `alt_art_variant: 2`
