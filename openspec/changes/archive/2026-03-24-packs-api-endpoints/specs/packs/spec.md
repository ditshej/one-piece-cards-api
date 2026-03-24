## MODIFIED Requirements

### Requirement: Pack listing
The system SHALL provide a list of all available packs via the API, ordered by id.

#### Scenario: List all packs
- **GIVEN** multiple packs exist in the database
- **WHEN** `GET /api/v1/packs` is requested
- **THEN** all packs are returned ordered by their id
- **AND** each pack includes id and name
- **AND** the response status is 200
