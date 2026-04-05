## Why

The API and the MCP server are publicly accessible. Since it runs on private hosting, third parties can use it and cause traffic/costs. The project is to become open source, but everyone should run their own instance — not the author's.

## What Changes

- `ApiKeyMiddleware` protects all `/api/v1/*` routes and the MCP endpoint `/mcp`
- API key is configured as a static value in `.env` (`API_KEY`)
- Requests must send `Authorization: Bearer <key>` header
- Requests without or with the wrong key receive 401
- Scramble docs (`/docs/api*`) remain public — they return no data

## Capabilities

### New Capabilities

- `api-key-auth`: Static API key authentication for REST API and MCP server

### Modified Capabilities

<!-- no existing specs affected -->

## Impact

- All existing API tests must send the auth header
- MCP integration in other projects needs `Authorization: Bearer <key>` header
- No breaking change for the MCP server (header support is built into `laravel/mcp`)
- No database changes, no new package

## Non-goals

- Per-user token management (no Sanctum/Passport)
- Rate limiting
- Token rotation or expiry
- Protection of Scramble docs
