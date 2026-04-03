## Why

The API documentation at `/docs/api` is permanently locked to light mode, ignoring the user's OS-level dark mode preference. The landing page (`/`) already follows `prefers-color-scheme` via Tailwind's `dark:` variants, so the docs UI is visually inconsistent with the rest of the project.

## What Changes

- Set Scramble's `ui.theme` config value from `'light'` to `'system'` so the docs UI reads the OS dark mode preference on load and responds to live changes.
- No custom CSS, no new assets, and no changes to the published Blade template — the system-theme JavaScript is already present in `resources/views/vendor/scramble/docs.blade.php` and activates automatically when `theme` is `system`.

## Capabilities

### New Capabilities

- `docs-dark-mode`: Interactive API docs at `/docs/api` automatically switch between light and dark themes based on `prefers-color-scheme`, matching the system preference without any user action.

### Modified Capabilities

- `api-docs`: The docs UI behaviour changes — theme is no longer static (`light`) but system-driven. The existing requirement "page renders an interactive UI showing all API endpoints" is not affected, but the visual presentation now adapts to the OS setting. A delta spec captures this new requirement.

## Impact

- **Config**: `config/scramble.php` — `ui.theme` value changes from `'light'` to `'system'`.
- **No API changes**: No endpoints, no JSON responses, no auth behaviour affected.
- **No frontend build required**: Stoplight Elements handles theming via `data-theme` attribute; no Vite/Tailwind compilation needed for the docs page itself.
- **Non-goals**: Custom theme toggle UI, persisting user preference in localStorage, or theming any page other than `/docs/api`.
