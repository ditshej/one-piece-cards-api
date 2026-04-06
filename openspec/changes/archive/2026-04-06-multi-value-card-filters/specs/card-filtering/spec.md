## ADDED Requirements

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
