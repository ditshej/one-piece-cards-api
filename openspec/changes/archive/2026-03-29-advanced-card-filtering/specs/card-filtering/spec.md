# Card Filtering Specification

## Purpose
Comprehensive filtering of card data via the REST API and MCP tool.

## Requirements

### Requirement: Filter by name
The system SHALL support filtering cards by name using a case-insensitive partial match.

#### Scenario: Filter cards by partial name
- **GIVEN** cards "Monkey D. Luffy" and "Roronoa Zoro" exist
- **WHEN** `GET /api/v1/cards?name=luffy` is called
- **THEN** only "Monkey D. Luffy" is returned

---

### Requirement: Filter by rarity
The system SHALL support filtering cards by exact rarity value.

#### Scenario: Filter cards by rarity
- **GIVEN** cards with rarity "Uncommon" and "Rare" exist
- **WHEN** `GET /api/v1/cards?rarity=Uncommon` is called
- **THEN** only cards with rarity "Uncommon" are returned

---

### Requirement: Filter by attribute
The system SHALL support filtering cards by a single attribute using JSON array containment.

#### Scenario: Filter cards by attribute
- **GIVEN** cards with attributes ["Wisdom"] and ["Strike"] exist
- **WHEN** `GET /api/v1/cards?attribute=Wisdom` is called
- **THEN** only cards containing "Wisdom" in their attributes array are returned

---

### Requirement: Filter by type
The system SHALL support filtering cards by a single type using JSON array containment.

#### Scenario: Filter cards by type
- **GIVEN** cards with types ["Egghead"] and ["Straw Hat Crew"] exist
- **WHEN** `GET /api/v1/cards?type=Egghead` is called
- **THEN** only cards containing "Egghead" in their types array are returned

---

### Requirement: Filter by pack label
The system SHALL support filtering cards by pack label (e.g., "OP-15"), not by internal pack ID.

#### Scenario: Filter by pack label
- **GIVEN** a pack with label "OP-01" exists with cards
- **WHEN** `GET /api/v1/cards?pack=OP-01` is called
- **THEN** only cards from that pack are returned

---

### Requirement: Filter by cost range
The system SHALL support filtering cards by minimum and/or maximum cost.

#### Scenario: Filter by minimum cost
- **GIVEN** cards with cost 4, 6, and 8 exist
- **WHEN** `GET /api/v1/cards?cost_min=6` is called
- **THEN** only cards with cost ≥ 6 are returned

#### Scenario: Filter by cost range
- **GIVEN** cards with cost 2, 5, and 9 exist
- **WHEN** `GET /api/v1/cards?cost_min=4&cost_max=6` is called
- **THEN** only the card with cost 5 is returned

---

### Requirement: Filter by power range
The system SHALL support filtering cards by minimum and/or maximum power.

#### Scenario: Filter by minimum power
- **GIVEN** cards with power 2000, 5000, and 9000 exist
- **WHEN** `GET /api/v1/cards?power_min=5000` is called
- **THEN** only cards with power ≥ 5000 are returned

---

### Requirement: Filter by TCG keyword
The system SHALL support filtering cards by TCG keyword, matching `[Keyword]` in effect or trigger text. This ensures cards that merely *mention* a keyword in a negative context are not included.

#### Scenario: Filter by keyword Blocker
- **GIVEN** a card with effect "[Blocker] ..." exists
- **AND** a card with effect "your opponent cannot activate a [Blocker] character" exists
- **WHEN** `GET /api/v1/cards?keyword=Blocker` is called
- **THEN** only the card that *has* the [Blocker] keyword is returned
- **AND** the card that merely mentions [Blocker] in its effect is NOT returned

---

### Requirement: Filter alt art cards
The system SHALL support filtering to only alt art cards (those with `_p` suffix in their ID).

#### Scenario: Filter alt art cards
- **GIVEN** cards "OP13-113" and "OP13-113_p1" exist
- **WHEN** `GET /api/v1/cards?alt_art=true` is called
- **THEN** only "OP13-113_p1" is returned

---

### Requirement: Pagination size control
The system SHALL support a `per_page` parameter (integer, 1–100) to control results per page.

---

### Requirement: Filter validation
The system SHALL validate all filter parameters and return a 422 response with error details for invalid values.

#### Scenario: Invalid rarity value
- **WHEN** `GET /api/v1/cards?rarity=InvalidRarity` is called
- **THEN** a 422 response is returned

#### Scenario: Non-numeric cost_min
- **WHEN** `GET /api/v1/cards?cost_min=abc` is called
- **THEN** a 422 response is returned

---

### Requirement: Combinable filters
All filters SHALL be combinable with AND logic.

#### Scenario: Combined color, rarity, and pack filters
- **GIVEN** cards from OP-01 and OP-02 with various rarities and colors exist
- **WHEN** `GET /api/v1/cards?pack=OP-01&color=Red&rarity=Uncommon` is called
- **THEN** only Red Uncommon cards from OP-01 are returned
