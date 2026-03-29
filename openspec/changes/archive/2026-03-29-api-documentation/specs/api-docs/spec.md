## ADDED Requirements

### Requirement: OpenAPI spec is served at /docs/api.json
The system SHALL serve a valid OpenAPI 3.1 JSON document at `GET /docs/api.json` that describes all `/api/v1/*` endpoints.

#### Scenario: OpenAPI spec is accessible
- **WHEN** a client sends `GET /docs/api.json`
- **THEN** the response has status 200 and `Content-Type: application/json`
- **THEN** the response body contains a valid OpenAPI 3.1 document with all v1 endpoints listed

### Requirement: Interactive API documentation is served at /docs/api
The system SHALL serve an interactive HTML documentation UI at `GET /docs/api` using Stoplight Elements.

#### Scenario: Docs UI is accessible
- **WHEN** a client sends `GET /docs/api`
- **THEN** the response has status 200 and `Content-Type: text/html`
- **THEN** the page renders an interactive UI showing all API endpoints

### Requirement: All v1 endpoints are documented
The OpenAPI spec SHALL include all four endpoints: `GET /api/v1/packs`, `GET /api/v1/packs/{pack}`, `GET /api/v1/cards`, `GET /api/v1/cards/{card}`.

#### Scenario: Packs endpoints in spec
- **WHEN** the OpenAPI spec is fetched
- **THEN** it contains paths for `/api/v1/packs` and `/api/v1/packs/{pack}`

#### Scenario: Cards endpoints with query parameters in spec
- **WHEN** the OpenAPI spec is fetched
- **THEN** it contains a path for `/api/v1/cards` with query parameters: `color`, `category`, `cost`, `pack`, `search`
