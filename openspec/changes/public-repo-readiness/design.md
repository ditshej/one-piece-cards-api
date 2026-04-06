## Context

The repository currently has no LICENSE file, no CONTRIBUTING guide, and no CHANGELOG. Two docs-folder files (`dev-setup.md`, `implementation-roadmap.md`) are generic/internal artifacts that add noise for public contributors. All other source code and specs are in good shape.

## Goals / Non-Goals

**Goals:**
- Meet minimum open-source conventions expected by the community
- Give contributors a clear starting point (workflow, rules, how to run tests)
- Remove files that don't belong in a public project repo

**Non-Goals:**
- Changing API behavior or code
- Maintaining a full detailed changelog (initial entry covers the complete feature set)
- Writing vegapull installation instructions (link to official docs)

## Decisions

**MIT License** — matches `composer.json`. No alternatives needed; straightforward.

**CONTRIBUTING.md structure** — focuses on the OpenSpec workflow as the central contribution process. Contributors must start with `/opsx:propose` before any implementation. This is the most important thing to communicate. Also covers: feature branch convention, TDD requirement, running tests, code style (Pint).

**CHANGELOG.md** — uses [Keep a Changelog](https://keepachangelog.com) format with a single `[Unreleased]` section at the top and a `[1.0.0]` entry that groups all existing functionality. No specific release date needed for initial entry — can be left as `YYYY-MM-DD` until the first tagged release.

**Remove `docs/dev-setup.md`** — it's a generic Laravel project setup template maintained in the yohohoho repo. It adds no value for someone using this specific API project. Contributors don't need to set up a new Laravel project; they clone this one.

**Remove `docs/implementation-roadmap.md`** — internal planning artifact. Completed work is tracked via archived OpenSpec changes. The roadmap doesn't reflect current priorities.

## Risks / Trade-offs

- Removing `docs/dev-setup.md` means anyone who copied the file from this repo loses the reference → Low risk; yohohoho is the canonical source.
- CHANGELOG requires manual maintenance going forward → Acceptable; it's a lightweight convention to uphold.

## Open Questions

*(none)*
