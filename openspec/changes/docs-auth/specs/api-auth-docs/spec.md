## ADDED Requirements

### Requirement: OpenAPI spec includes a Bearer token security scheme
The system SHALL include a `BearerToken` HTTP security scheme in `components.securitySchemes` of the generated OpenAPI 3.1 document, with `scheme: bearer` and `bearerFormat: API Key`.

#### Scenario: Security scheme present in spec
- **WHEN** a client fetches `GET /docs/api.json`
- **THEN** the response body contains `components.securitySchemes.BearerToken` with `type: http`, `scheme: bearer`

### Requirement: All API endpoints require Bearer token authentication in the spec
The system SHALL include a global `security` field in the OpenAPI 3.1 document that applies the `BearerToken` scheme to all endpoints.

#### Scenario: Global security requirement in spec
- **WHEN** a client fetches `GET /docs/api.json`
- **THEN** the response body contains a top-level `security` array with `[{"BearerToken": []}]`

### Requirement: Docs UI shows authentication controls
The system SHALL render the Stoplight Elements UI at `/docs/api` with an "Authorize" button that allows users to enter a Bearer token for use in "Try It" requests.

#### Scenario: Authorize button visible in docs UI
- **WHEN** a client navigates to `GET /docs/api`
- **THEN** the page renders with an "Authorize" button or authentication control derived from the `BearerToken` security scheme
