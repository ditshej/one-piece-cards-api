## MODIFIED Requirements

### Requirement: Card Model
The system SHALL store card data with the following attributes:
- `id` (string, unique) — Card identifier from vegapull (e.g., "OP01-001")
- `pack_id` (string) — Reference to the pack/set the card belongs to
- `card_set` (string, nullable) — Origin set prefix derived from the card ID (e.g., "OP03" from "OP03-072")
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
- `alt_art_variant` (integer, nullable) — Variant number for alt art cards (1 for _p1, 2 for _p2, null otherwise)

#### Scenario: Create card with all attributes
- **WHEN** a card is created with all required attributes
- **THEN** the card is persisted with the correct values for each field

#### Scenario: card_set and alt_art_variant derived automatically
- **WHEN** a card with id `OP03-072_p1` is created
- **THEN** `card_set` is `OP03` and `alt_art_variant` is `1`
