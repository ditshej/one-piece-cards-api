## MODIFIED Requirements

### Requirement: API requests require a valid Bearer token
The system SHALL reject requests to `/api/v1/*` without a valid `Authorization: Bearer <token>` header with HTTP 401. Tokens SHALL be validated via Laravel Sanctum Personal Access Tokens (SHA-256 hash lookup against `personal_access_tokens` table, bound to a User record).

#### Scenario: Request without Authorization header returns 401
- **WHEN** a client sends `GET /api/v1/packs` without an Authorization header
- **THEN** the response has status 401

#### Scenario: Request with invalid token returns 401
- **WHEN** a client sends `GET /api/v1/packs` with `Authorization: Bearer invalid-token`
- **THEN** the response has status 401

#### Scenario: Request with valid Sanctum token returns 200
- **WHEN** a client sends `GET /api/v1/packs` with a valid `Authorization: Bearer <token>` matching a Sanctum Personal Access Token
- **THEN** the response has status 200

### Requirement: MCP endpoint requires a valid Bearer token
The system SHALL reject requests to `POST /mcp` without a valid `Authorization: Bearer <token>` header with HTTP 401. Token validation uses the same Sanctum mechanism as API routes.

#### Scenario: MCP request without Authorization header returns 401
- **WHEN** a client sends `POST /mcp` without an Authorization header
- **THEN** the response has status 401

#### Scenario: MCP request with valid token succeeds
- **WHEN** a client sends `POST /mcp` with a valid `Authorization: Bearer <token>` matching a Sanctum Personal Access Token
- **THEN** the MCP server processes the request normally
