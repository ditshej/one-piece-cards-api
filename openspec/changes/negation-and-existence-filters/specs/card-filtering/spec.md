## ADDED Requirements

### Requirement: Filter by excluding colors using array notation
The system SHALL accept `color_not[]` and return only cards whose colors array contains none of the specified values.

#### Scenario: Exclude a single color
- **WHEN** `GET /api/v1/cards?color_not[]=Red` is called
- **THEN** only cards whose colors array does not contain "Red" are returned

#### Scenario: Exclude multiple colors
- **WHEN** `GET /api/v1/cards?color_not[]=Red&color_not[]=Blue` is called
- **THEN** only cards whose colors array contains neither "Red" nor "Blue" are returned

---

### Requirement: Filter by excluding rarities using array notation
The system SHALL accept `rarity_not[]` and return only cards whose rarity is not in the specified list.

#### Scenario: Exclude multiple rarities
- **WHEN** `GET /api/v1/cards?rarity_not[]=C&rarity_not[]=UC` is called
- **THEN** only cards with rarity other than "C" or "UC" are returned

---

### Requirement: Filter by excluding card sets using array notation
The system SHALL accept `card_set_not[]` and return only cards not belonging to the specified sets.

#### Scenario: Exclude a card set
- **WHEN** `GET /api/v1/cards?card_set_not[]=OP01` is called
- **THEN** only cards whose card_set is not "OP01" are returned

---

### Requirement: Filter by excluding categories using array notation
The system SHALL accept `category_not[]` and return only cards not matching the specified categories. Invalid values return 422.

#### Scenario: Exclude a category
- **WHEN** `GET /api/v1/cards?category_not[]=Leader` is called
- **THEN** only cards whose category is not "Leader" are returned

#### Scenario: Invalid category_not value returns 422
- **WHEN** `GET /api/v1/cards?category_not[]=Invalid` is called
- **THEN** a 422 response is returned

---

### Requirement: Filter by excluding types using array notation
The system SHALL accept `type_not[]` and return only cards whose types array contains none of the specified values.

#### Scenario: Exclude a type
- **WHEN** `GET /api/v1/cards?type_not[]=Navy` is called
- **THEN** only cards whose types array does not contain "Navy" are returned

---

### Requirement: Filter by excluding attributes using array notation
The system SHALL accept `attribute_not[]` and return only cards whose attributes array contains none of the specified values.

#### Scenario: Exclude a single attribute
- **WHEN** `GET /api/v1/cards?attribute_not[]=Slash` is called
- **THEN** only cards whose attributes array does not contain "Slash" are returned

#### Scenario: Exclude multiple attributes
- **WHEN** `GET /api/v1/cards?attribute_not[]=Slash&attribute_not[]=Strike` is called
- **THEN** only cards whose attributes array contains neither "Slash" nor "Strike" are returned

---

### Requirement: Filter by excluding keywords using array notation
The system SHALL accept `keyword_not[]` and return only cards that have none of the specified keywords (in bracket notation) in their effect or trigger text.

#### Scenario: Exclude a keyword
- **WHEN** `GET /api/v1/cards?keyword_not[]=Blocker` is called
- **THEN** only cards with no [Blocker] in effect AND no [Blocker] in trigger are returned

#### Scenario: Combine keyword with keyword_not
- **WHEN** `GET /api/v1/cards?has_trigger=true&keyword_not[]=Blocker` is called
- **THEN** only cards that have a trigger but do not contain [Blocker] in effect or trigger are returned

---

### Requirement: Filter by excluding cost values using array notation
The system SHALL accept `cost_not[]` and return only cards whose cost is not in the specified list.

#### Scenario: Exclude specific costs
- **WHEN** `GET /api/v1/cards?cost_not[]=9&cost_not[]=10` is called
- **THEN** only cards with cost other than 9 or 10 are returned

#### Scenario: Invalid cost_not value returns 422
- **WHEN** `GET /api/v1/cards?cost_not[]=abc` is called
- **THEN** a 422 response is returned

---

### Requirement: Filter by excluding power values using array notation
The system SHALL accept `power_not[]` and return only cards whose power is not in the specified list.

#### Scenario: Exclude specific power values
- **WHEN** `GET /api/v1/cards?power_not[]=9000&power_not[]=10000` is called
- **THEN** only cards with power other than 9000 or 10000 are returned

#### Scenario: Invalid power_not value returns 422
- **WHEN** `GET /api/v1/cards?power_not[]=abc` is called
- **THEN** a 422 response is returned

---

### Requirement: Filter cards by exact counter value using array notation
The system SHALL accept `counter[]` and return only cards whose counter matches any of the specified values.

#### Scenario: Filter by a single counter value
- **WHEN** `GET /api/v1/cards?counter[]=1000` is called
- **THEN** only cards with counter 1000 are returned

#### Scenario: Filter by multiple counter values
- **WHEN** `GET /api/v1/cards?counter[]=1000&counter[]=2000` is called
- **THEN** only cards with counter 1000 or 2000 are returned

#### Scenario: Invalid counter value returns 422
- **WHEN** `GET /api/v1/cards?counter[]=abc` is called
- **THEN** a 422 response is returned

---

### Requirement: Filter by excluding counter values using array notation
The system SHALL accept `counter_not[]` and return only cards whose counter is not in the specified list.

#### Scenario: Exclude a counter value
- **WHEN** `GET /api/v1/cards?counter_not[]=2000` is called
- **THEN** only cards with counter other than 2000 are returned (including cards with null counter)

---

### Requirement: Filter cards by trigger existence
The system SHALL accept a `has_trigger` boolean and return only cards that have (or do not have) a trigger value.

#### Scenario: Filter cards with a trigger
- **WHEN** `GET /api/v1/cards?has_trigger=true` is called
- **THEN** only cards with a non-null trigger are returned

#### Scenario: Filter cards without a trigger
- **WHEN** `GET /api/v1/cards?has_trigger=false` is called
- **THEN** only cards with a null trigger are returned

---

### Requirement: Filter cards by effect existence
The system SHALL accept a `has_effect` boolean and return only cards that have (or do not have) an effect value.

#### Scenario: Filter cards with an effect
- **WHEN** `GET /api/v1/cards?has_effect=true` is called
- **THEN** only cards with a non-null effect are returned

#### Scenario: Filter cards without an effect
- **WHEN** `GET /api/v1/cards?has_effect=false` is called
- **THEN** only cards with a null effect are returned

---

### Requirement: Filter cards by counter existence
The system SHALL accept a `has_counter` boolean and return only cards that have (or do not have) a counter value.

#### Scenario: Filter cards with a counter
- **WHEN** `GET /api/v1/cards?has_counter=true` is called
- **THEN** only cards with a non-null counter are returned

#### Scenario: Filter cards without a counter
- **WHEN** `GET /api/v1/cards?has_counter=false` is called
- **THEN** only cards with a null counter are returned (Leaders, Events, Stages)
