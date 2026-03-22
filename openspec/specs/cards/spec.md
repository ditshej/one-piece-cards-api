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

### Requirement: Card belongs to Pack
Each card SHALL belong to exactly one pack.

#### Scenario: Card with valid pack reference
- GIVEN a card with pack_id "OP01"
- WHEN the card is retrieved
- THEN the associated pack data is accessible via the relationship

### Requirement: Card searchability
Cards SHALL be searchable by color, category, cost, type, and effect text.

#### Scenario: Filter cards by color
- GIVEN multiple cards with different colors exist
- WHEN a request filters by color "Red"
- THEN only cards containing "Red" in their colors array are returned
- AND the response status is 200

#### Scenario: Full-text search in effect text
- GIVEN cards with various effect texts exist
- WHEN a request searches for "draw 2 cards"
- THEN cards whose effect text contains the search term are returned
- AND the response status is 200
