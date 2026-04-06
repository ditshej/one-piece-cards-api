# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

### Bug Fixes
- Adapt import and fetch to real vegapull JSON format
- Remove artisan optimize from deploy (path mismatch on shared hosting)
- Register ai routes so MCP endpoint is reachable
- Add laravel/mcp as production dependency
- Handle zero as valid cost filter value in scopeApplyFilters
- Normalize null cost to 0 for Event cards at import and in DB
- Show /mcp as code snippet instead of browser link
- Show full MCP server URL instead of relative path
- Move MCP URL to its own line for readability
- Build and rsync frontend assets as part of deploy
- Replace deprecated ValidateCsrfToken with PreventRequestForgery in sanctum config
- Use configurable DEPLOY_PHP path in create-token.sh
- Pass DEPLOY_PHP from .env.deploy to _deploy.sh via SSH


### Features
- Add Pack and Card models with migrations and factories
- Add cards:import artisan command for vegapull JSON
- Add packs API endpoints with v1 routing infrastructure
- Add cards API endpoints with filtering, search, and pagination
- Add database seeder with pack and card factories
- Add scheduled import and architecture tests
- Add cards:fetch command for vegapull integration
- Add metanet deploy scripts and update roadmap
- Add cards:sync command to upload SQLite DB to production
- Add Scramble API documentation
- Add MCP server with card and pack tools
- Add API key authentication for REST API and MCP server
- Add advanced card filtering to API and MCP tool
- Add card_set and alt_art_variant fields to cards
- Replace laravel welcome page with api landing page
- Improve api landing page visual — diagonal fan, outline, larger text
- Improve api landing page visual — dark/light mode polish
- Add bearer token security scheme to api documentation
- Add configurable contact info to api documentation
- Default contact URL to APP_URL in api docs config
- Append contact info to api docs description
- Set docs theme to system (respects OS dark/light mode)
- Replace single API key with Sanctum multi-token auth
- Add create-token.sh script, update README and API docs description
- Add --revoke flag to create-token.sh
- Add token:revoke command and refactor create-token.sh
- Add TDD enforcement via pre-commit hook and arch test
- **public-repo-readiness:** Add LICENSE, CONTRIBUTING, CHANGELOG, clean up internal docs
- **multi-value-card-filters:** Support array notation for cost filter
- **multi-value-filters-extended:** Extend all filters to accept array notation


