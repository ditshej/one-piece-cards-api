## Why

Die API ist live auf `op-cards.ditshej.ch` aber vollständig undokumentiert. Andere Projekte (z.B. AI-Assistenten, der Brook Deck Simulator) müssen die Endpoints manuell erkunden. Eine maschinenlesbare OpenAPI-Spec macht die API für LLMs direkt konsumierbar ohne manuelle Erklärungen.

## What Changes

- `dedoc/scramble` installieren (zero-config OpenAPI-Generator für Laravel)
- Automatisch generierte interaktive Dokumentation unter `/docs/api` (Stoplight Elements UI)
- OpenAPI 3.1 JSON-Spec unter `/docs/api.json`
- Scramble-Config publishen und auf die API anpassen (Titel, Version, API-Präfix `/api/v1`)

## Capabilities

### New Capabilities

- `api-docs`: Automatisch generierte, interaktive API-Dokumentation via Scramble — HTML-UI für Menschen, OpenAPI-JSON für AI und Tooling

### Modified Capabilities

<!-- keine bestehenden Specs betroffen -->

## Impact

- Neue Composer-Dependency: `dedoc/scramble`
- Zwei neue Routen: `GET /docs/api` (UI) und `GET /docs/api.json` (OpenAPI-Spec)
- Kein Breaking Change — nur additive Änderungen
- Kein Einfluss auf bestehende API-Routen unter `/api/v1/*`

## Non-goals

- Manuelle Annotations oder DocBlocks in Controllern schreiben
- Auth/Rate-Limiting dokumentieren (nicht vorhanden)
- Versionierung der Docs-Artefakte (generierte Dateien werden nicht committed)
