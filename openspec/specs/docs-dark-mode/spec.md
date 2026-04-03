## Requirements

### Requirement: Docs UI respects system dark mode preference
The docs page at `/docs/api` SHALL automatically apply a dark theme when the OS/browser dark mode preference (`prefers-color-scheme: dark`) is active, and a light theme otherwise.

#### Scenario: User visits docs with system dark mode enabled
- **WHEN** a `GET /docs/api` request is made by a browser that reports `prefers-color-scheme: dark`
- **THEN** the page renders with `data-theme="dark"` on the `<html>` element

#### Scenario: User visits docs with system light mode enabled
- **WHEN** a `GET /docs/api` request is made by a browser that reports `prefers-color-scheme: light`
- **THEN** the page renders with `data-theme="light"` on the `<html>` element

### Requirement: Docs UI responds to live OS preference changes
The docs page SHALL update its theme without a page reload when the user changes their OS dark/light mode preference while the page is open.

#### Scenario: User switches OS to dark mode while docs are open
- **WHEN** the OS dark mode preference changes to dark while `/docs/api` is open
- **THEN** the page updates `data-theme` to `"dark"` without requiring a reload

#### Scenario: User switches OS to light mode while docs are open
- **WHEN** the OS dark mode preference changes to light while `/docs/api` is open
- **THEN** the page updates `data-theme` to `"light"` without requiring a reload
