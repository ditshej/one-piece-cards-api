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

### Requirement: Filter by card_set
The system SHALL support filtering cards by `card_set` using an exact match on the derived `card_set` column.

#### Scenario: Filter cards by card_set
- **GIVEN** cards from sets "OP01" and "OP02" exist
- **WHEN** `GET /api/v1/cards?card_set=OP01` is called
- **THEN** only cards with `card_set` "OP01" are returned

#### Scenario: Filter by card_set with no results
- **GIVEN** no cards from set "OP99" exist
- **WHEN** `GET /api/v1/cards?card_set=OP99` is called
- **THEN** an empty result set is returned with status 200

---

### Requirement: Filter alt art cards
The system SHALL support filtering to only alt art cards using the `alt_art_variant` column (non-null value indicates an alt art card).

#### Scenario: Filter alt art cards
- **GIVEN** cards "OP13-113" (alt_art_variant null) and "OP13-113_p1" (alt_art_variant "p1") exist
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

### Requirement: Filter by cost using multiple discrete values
The system SHALL accept an array of cost values via `?cost[]=N` notation and return only cards whose cost matches any of the specified values.

#### Scenario: Filter by two specific cost values
- **WHEN** `GET /api/v1/cards?cost[]=3&cost[]=5` is called
- **THEN** only cards with cost 3 or cost 5 are returned

#### Scenario: Single-value array is equivalent to scalar cost filter
- **WHEN** `GET /api/v1/cards?cost[]=5` is called
- **THEN** only cards with cost 5 are returned (same result as `?cost=5`)

#### Scenario: Invalid array item returns 422
- **WHEN** `GET /api/v1/cards?cost[]=abc` is called
- **THEN** a 422 response is returned

---

### Requirement: Combinable filters
All filters SHALL be combinable with AND logic.

#### Scenario: Combined color, rarity, and pack filters
- **GIVEN** cards from OP-01 and OP-02 with various rarities and colors exist
- **WHEN** `GET /api/v1/cards?pack=OP-01&color=Red&rarity=Uncommon` is called
- **THEN** only Red Uncommon cards from OP-01 are returned

---

### Requirement: Filter by multiple colors using array notation
The system SHALL accept an array of color values and return cards that contain at least one of the specified colors.

#### Scenario: Filter by two colors
- **WHEN** `GET /api/v1/cards?color[]=Red&color[]=Yellow` is called
- **THEN** only cards whose colors array contains Red OR Yellow are returned

#### Scenario: Single-element color array equivalent to scalar
- **WHEN** `GET /api/v1/cards?color[]=Red` is called
- **THEN** the result is identical to `?color=Red`

---

### Requirement: Filter by multiple rarities using array notation
The system SHALL accept an array of rarity values and return cards matching any of them.

#### Scenario: Filter by two rarities
- **WHEN** `GET /api/v1/cards?rarity[]=SR&rarity[]=SEC` is called
- **THEN** only cards with rarity SR or SEC are returned

---

### Requirement: Filter by multiple card sets using array notation
The system SHALL accept an array of card_set values and return cards from any of the specified sets.

#### Scenario: Filter by two card sets
- **WHEN** `GET /api/v1/cards?card_set[]=OP13&card_set[]=OP15` is called
- **THEN** only cards with card_set OP13 or OP15 are returned

---

### Requirement: Filter by multiple categories using array notation
The system SHALL accept an array of category values and return cards matching any of them. Valid values remain Character, Event, Leader, Stage.

#### Scenario: Filter by two categories
- **WHEN** `GET /api/v1/cards?category[]=Character&category[]=Leader` is called
- **THEN** only cards with category Character or Leader are returned

#### Scenario: Invalid category value returns 422
- **WHEN** `GET /api/v1/cards?category[]=Invalid` is called
- **THEN** a 422 response is returned

---

### Requirement: Filter by multiple types using array notation
The system SHALL accept an array of type values and return cards whose types array contains at least one of the specified types.

#### Scenario: Filter by two types
- **WHEN** `GET /api/v1/cards?type[]=Minks&type[]=Strawhats` is called
- **THEN** only cards whose types array contains Minks OR Strawhats are returned

---

### Requirement: Filter by multiple attributes using array notation
The system SHALL accept an array of attribute values and return cards whose attributes array contains at least one of the specified attributes.

#### Scenario: Filter by two attributes
- **WHEN** `GET /api/v1/cards?attribute[]=Wisdom&attribute[]=Strike` is called
- **THEN** only cards whose attributes array contains Wisdom OR Strike are returned

---

### Requirement: Filter by multiple keywords using array notation
The system SHALL accept an array of keyword values and return cards that have at least one of the specified keywords (in bracket notation) in their effect or trigger text.

#### Scenario: Filter by two keywords
- **WHEN** `GET /api/v1/cards?keyword[]=Blocker&keyword[]=Rush` is called
- **THEN** only cards with [Blocker] OR [Rush] in their effect or trigger are returned

---

### Requirement: Filter by exact power value using array notation
The system SHALL accept a `power` param (single integer or array) and return cards matching any of the specified power values.

#### Scenario: Filter by two power values
- **WHEN** `GET /api/v1/cards?power[]=8000&power[]=10000` is called
- **THEN** only cards with power 8000 or 10000 are returned

#### Scenario: Single-element power array equivalent to scalar
- **WHEN** `GET /api/v1/cards?power[]=8000` is called
- **THEN** only cards with power 8000 are returned

#### Scenario: Invalid power array item returns 422
- **WHEN** `GET /api/v1/cards?power[]=abc` is called
- **THEN** a 422 response is returned

#### Scenario: power exact-match and power_min/power_max are combinable
- **WHEN** `GET /api/v1/cards?power[]=8000&power_min=5000` is called
- **THEN** both filters are applied with AND logic
