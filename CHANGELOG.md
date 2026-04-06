# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.0.0] - 2025-04-06

### Added

- REST API v1 endpoints: `GET /api/v1/packs`, `GET /api/v1/packs/{pack}`, `GET /api/v1/cards`, `GET /api/v1/cards/{card}`
- Card filtering by color, category, cost, pack, and full-text search on effect text
- Pagination support on card listing endpoint
- Token-based authentication via Laravel Sanctum (Bearer token)
- `cards:fetch` artisan command — fetches card data from Bandai via vegapull and imports directly
- `cards:import` artisan command — imports card data from existing vegapull JSON files
- `cards:sync` artisan command — uploads local SQLite database to production server via SCP
- `token:create` and `token:revoke` artisan commands for API token management
- Optional scheduled weekly import (`IMPORT_SCHEDULE_ENABLED` in `.env`)
- MCP server at `/mcp` exposing `list-packs`, `get-pack`, `list-cards`, `get-card` tools
- Auto-generated API documentation via Scramble at `/docs/api`
- Deployment scripts (`deploy.sh`, `_deploy.sh`) for shared hosting
