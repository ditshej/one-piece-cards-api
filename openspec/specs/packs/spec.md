# Packs Specification

## Purpose
Management of OPTCG card packs (sets/booster packs). Packs group cards into release sets.

## Requirements

### Requirement: Pack Model
The system SHALL store pack data with the following attributes:
- `id` (string, unique) — Pack identifier (e.g., "OP01", "OP15", "ST01")
- `name` (string) — Pack display name (e.g., "Romance Dawn", "A Fist of Divine Speed")

### Requirement: Pack has many Cards
Each pack SHALL have a one-to-many relationship with cards.

#### Scenario: Retrieve pack with cards
- GIVEN a pack "OP01" with 121 cards
- WHEN the pack is retrieved with cards
- THEN all 121 associated cards are returned

### Requirement: Pack listing
The system SHALL provide a list of all available packs.

#### Scenario: List all packs
- GIVEN multiple packs exist in the database
- WHEN a request to list packs is made
- THEN all packs are returned ordered by their id
- AND the response status is 200
