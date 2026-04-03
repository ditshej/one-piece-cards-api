## ADDED Requirements

### Requirement: API landing page replaces default welcome page
The root URL (`/`) SHALL display a custom landing page that orients developers to the API — not the default Laravel welcome page.

#### Scenario: Developer visits the root URL
- **WHEN** a GET request is made to `/`
- **THEN** the response is HTTP 200 with an HTML page titled "One Piece Cards API"

### Requirement: Left panel shows authentication instructions
The landing page SHALL display how to authenticate against the API using a Bearer token.

#### Scenario: Authentication section is visible
- **WHEN** the landing page is loaded
- **THEN** the page shows "Authorization: Bearer <your-api-key>" in a monospace code style

### Requirement: Left panel links to API documentation
The landing page SHALL link to the interactive API documentation at `/docs/api`.

#### Scenario: Documentation link is present
- **WHEN** the landing page is loaded
- **THEN** a visible link pointing to `/docs/api` is present on the page

### Requirement: Left panel links to MCP server
The landing page SHALL mention the MCP server endpoint at `/mcp`.

#### Scenario: MCP link is present
- **WHEN** the landing page is loaded
- **THEN** a reference to `/mcp` is visible on the page

### Requirement: Right panel shows project branding
The right panel SHALL display "One Piece TCG" as the wordmark and "API" with a layered fan-effect animation instead of the Laravel logo and "13".

#### Scenario: Project branding is rendered
- **WHEN** the landing page is loaded
- **THEN** the text "One Piece TCG" is visible in the right panel
- **THEN** the text "API" is visible with a layered animated effect in the right panel
- **THEN** the Laravel wordmark and "13" are no longer present
