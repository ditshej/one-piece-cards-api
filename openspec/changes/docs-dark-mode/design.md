## Context

Scramble's published Blade view (`resources/views/vendor/scramble/docs.blade.php`) already ships with full system-theme support. When `ui.theme` is set to `'system'`, the template:

1. Initialises `data-theme` and `color-scheme` from the current OS preference via `window.matchMedia('(prefers-color-scheme: dark)')`.
2. Attaches a live listener so the UI responds to preference changes without a page reload.

Currently `config/scramble.php` has `'theme' => 'light'`, so the system-theme branch of the Blade template is never reached.

## Goals / Non-Goals

**Goals:**
- Make `/docs/api` follow the OS dark mode preference automatically.
- Keep the change minimal — one config value, no template edits, no new assets.

**Non-Goals:**
- A manual toggle button in the docs UI.
- Persisting the user's theme choice in `localStorage` or a cookie.
- Theming any other page (landing page already handles this via Tailwind).

## Decisions

### Set `ui.theme` to `'system'` in `config/scramble.php`

The Scramble config exposes `light`, `dark`, and `system` as documented options. Choosing `system` delegates all detection logic to the existing JavaScript already in the published Blade view — no custom code is needed.

**Alternatives considered:**
- **Duplicate the matchMedia JS in a custom ServiceProvider**: More control, but duplicates code already maintained by Scramble. Rejected.
- **Publish a modified Blade template**: Not needed since the existing template already handles `system` correctly. Rejected.

## Risks / Trade-offs

- [Stoplight Elements dark mode fidelity] The Scramble template includes targeted CSS fixes for known dark-mode token colour issues in the Stoplight code viewer (e.g. `.token.property`, `.token.string`). These are already present and apply whenever `data-theme="dark"` is set — no additional risk.
- [Config cache] If the application has a cached config in production, a `php artisan config:clear` or `config:cache` re-run is required after deployment. → Low risk: standard Laravel deploy practice.
