## MODIFIED Requirements

### Requirement: Interactive API documentation is served at /docs/api
The system SHALL serve an interactive HTML documentation UI at `GET /docs/api` using Stoplight Elements. The UI SHALL use the `system` theme, automatically matching the user's OS dark/light mode preference.

#### Scenario: Docs UI is accessible
- **WHEN** a client sends `GET /docs/api`
- **THEN** the response has status 200 and `Content-Type: text/html`
- **THEN** the page renders an interactive UI showing all API endpoints

#### Scenario: Docs UI uses system theme setting
- **WHEN** a client sends `GET /docs/api`
- **THEN** the page's theme adapts to `prefers-color-scheme` rather than being permanently locked to light mode
