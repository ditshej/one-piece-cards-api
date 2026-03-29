## Requirements

### Requirement: API requests require a valid Bearer token
The system SHALL reject requests to `/api/v1/*` without a valid `Authorization: Bearer <key>` header with HTTP 401.

#### Scenario: Request without Authorization header returns 401
- **WHEN** a client sends `GET /api/v1/packs` without an Authorization header
- **THEN** the response has status 401

#### Scenario: Request with wrong key returns 401
- **WHEN** a client sends `GET /api/v1/packs` with `Authorization: Bearer wrong-key`
- **THEN** the response has status 401

#### Scenario: Request with correct key returns 200
- **WHEN** a client sends `GET /api/v1/packs` with the correct `Authorization: Bearer <API_KEY>`
- **THEN** the response has status 200

### Requirement: MCP endpoint requires a valid Bearer token
The system SHALL reject requests to `POST /mcp` without a valid `Authorization: Bearer <key>` header with HTTP 401.

#### Scenario: MCP request without Authorization header returns 401
- **WHEN** a client sends `POST /mcp` without an Authorization header
- **THEN** the response has status 401

#### Scenario: MCP request with correct key succeeds
- **WHEN** a client sends `POST /mcp` with the correct `Authorization: Bearer <API_KEY>`
- **THEN** the MCP server processes the request normally

### Requirement: Scramble docs remain publicly accessible
The system SHALL serve `/docs/api` and `/docs/api.json` without authentication.

#### Scenario: Docs UI accessible without auth
- **WHEN** a client sends `GET /docs/api` without an Authorization header
- **THEN** the response has status 200
