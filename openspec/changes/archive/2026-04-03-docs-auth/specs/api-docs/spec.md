## MODIFIED Requirements

### Requirement: OpenAPI spec is served at /docs/api.json
The system SHALL serve a valid OpenAPI 3.1 JSON document at `GET /docs/api.json` that describes all `/api/v1/*` endpoints, including `components.securitySchemes` with a `BearerToken` HTTP Bearer scheme and a global `security` requirement applied to all endpoints.

#### Scenario: OpenAPI spec is accessible
- **WHEN** a client sends `GET /docs/api.json`
- **THEN** the response has status 200 and `Content-Type: application/json`
- **THEN** the response body contains a valid OpenAPI 3.1 document with all v1 endpoints listed

#### Scenario: OpenAPI spec includes security scheme
- **WHEN** a client sends `GET /docs/api.json`
- **THEN** the response body contains `components.securitySchemes.BearerToken` with `type: http` and `scheme: bearer`
- **THEN** the response body contains a top-level `security` array referencing `BearerToken`
