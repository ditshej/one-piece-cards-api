## ADDED Requirements

### Requirement: CHANGELOG.md exists at repository root in Keep a Changelog format
The repository SHALL contain a `CHANGELOG.md` file following the [Keep a Changelog](https://keepachangelog.com) format with an `[Unreleased]` section and an initial `[1.0.0]` entry covering all existing functionality.

#### Scenario: Changelog is present and well-formed
- **WHEN** a user opens `CHANGELOG.md`
- **THEN** it SHALL have an `[Unreleased]` section at the top and at least one versioned entry

#### Scenario: Initial release entry covers existing features
- **WHEN** a contributor reads the `[1.0.0]` entry
- **THEN** it SHALL list the core capabilities: REST API endpoints, card data import via vegapull, token-based authentication, MCP server, and scheduled sync
