## ADDED Requirements

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
