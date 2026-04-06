## Why

The repository is ready to be made public so others can self-host their own One Piece TCG API using vegapull. Before publishing, a minimum set of open-source hygiene files is needed: a license, contribution guidelines, and a changelog.

## What Changes

- Add `LICENSE` (MIT) to the repository root
- Add `CONTRIBUTING.md` explaining how to contribute, including the OpenSpec workflow requirement and feature branch convention
- Add `CHANGELOG.md` (Keep a Changelog format) documenting existing functionality since the initial release
- Remove `docs/dev-setup.md` — this is a generic project template maintained in the yohohoho repo, not specific to this project
- Remove `docs/implementation-roadmap.md` — internal planning document not relevant to public contributors

## Capabilities

### New Capabilities

- `license`: MIT license file at repository root
- `contributing-guide`: Contributor documentation covering OpenSpec workflow, feature branch convention, TDD requirement, and how to run tests
- `changelog`: Version history in Keep a Changelog format documenting existing features

### Modified Capabilities

*(none — no existing spec requirements change)*

## Non-goals

- No changes to code, routes, or API behavior
- No vegapull installation guide (reference to official docs is sufficient)
- No README changes (separate concern)
- No changes to `docs/spatie-guidelines.md` (useful for contributors, stays in)
- No `openspec/` removal (required for ongoing development workflow)

## Impact

- Root directory: 3 new files (`LICENSE`, `CONTRIBUTING.md`, `CHANGELOG.md`)
- `docs/` directory: 2 files removed (`dev-setup.md`, `implementation-roadmap.md`)
- No API endpoints affected
- No code changes
