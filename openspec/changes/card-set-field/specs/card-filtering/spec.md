## ADDED Requirements

### Requirement: Filter by card_set
The system SHALL support filtering cards by origin set using the `card_set` query parameter (exact match).

#### Scenario: Filter cards by card_set
- **GIVEN** cards with ids `OP03-001`, `OP03-002`, and `OP01-001` exist
- **WHEN** `GET /api/v1/cards?card_set=OP03` is called
- **THEN** only the two OP03 cards are returned
- **AND** the response status is 200

#### Scenario: card_set filter finds reprints across packs
- **GIVEN** an OP03 card exists in pack "OP-03" and a reprint of the same set in pack "PRB-02"
- **WHEN** `GET /api/v1/cards?card_set=OP03` is called
- **THEN** both cards are returned regardless of their pack

---

## MODIFIED Requirements

### Requirement: Filter alt art cards
The system SHALL support filtering to only alt art cards. The filter SHALL use the `alt_art_variant` column (`IS NOT NULL`) rather than string-based ID inspection.

#### Scenario: Filter alt art cards
- **GIVEN** cards "OP13-113" (alt_art_variant: null) and "OP13-113_p1" (alt_art_variant: 1) exist
- **WHEN** `GET /api/v1/cards?alt_art=1` is called
- **THEN** only "OP13-113_p1" is returned
- **AND** the response status is 200
