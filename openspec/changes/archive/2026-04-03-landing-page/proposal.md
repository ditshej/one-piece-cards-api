## Why

The API root (`/`) currently shows the default Laravel welcome page, which is irrelevant for API consumers. Developers hitting the URL for the first time have no idea how to authenticate, what endpoints exist, or where the documentation is.

## What Changes

- Replace `resources/views/welcome.blade.php` content with a custom API landing page
- Keep the existing visual structure (layout, animations, dark mode) from the Laravel welcome page
- Replace left panel: API title, description, authentication instructions, links to docs and MCP
- Replace right panel SVGs: "One Piece TCG" instead of the Laravel wordmark, "API" with the same layered fan-effect animation instead of "13"

## Capabilities

### New Capabilities

- `landing-page`: A custom welcome page for the API root that introduces the API, explains authentication, lists key endpoints, and links to the interactive documentation and MCP server.

### Modified Capabilities

*(none — no existing spec-level requirements change)*

## Non-goals

- No new routes or controllers
- No authentication changes
- No changes to API behaviour
- No design system or component library — stays within the existing Blade + Tailwind setup

## Impact

- `resources/views/welcome.blade.php` — full content replacement
- `routes/web.php` — unchanged (still returns the `welcome` view)
- No database, migration, or API changes
