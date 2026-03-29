## Context

Die API hat 4 Endpoints unter `/api/v1/*` und ist vollständig undokumentiert. `dedoc/scramble` analysiert Laravel-Routen, Controller und Eloquent Resources automatisch und generiert daraus eine OpenAPI 3.1 Spec — ohne Annotations.

## Goals / Non-Goals

**Goals:**
- Interaktive Doku-UI unter `/docs/api` (Stoplight Elements)
- Maschinenlesbare OpenAPI-Spec unter `/docs/api.json`
- Zero-Annotation-Ansatz: kein manuelles Pflegen von Docblocks

**Non-Goals:**
- Authentifizierung der Docs-Route
- Versionierung der generierten Spec (nicht committed)
- Dokumentation von non-API-Routen

## Decisions

### Scramble statt Scribe

Scribe erfordert `@response`-Annotations und ist wartungsintensiv. Scramble liest die Eloquent Resources direkt aus und generiert die Spec vollautomatisch. Für diese API (saubere Resources, keine komplexe Auth) ist der Zero-Annotation-Ansatz ideal.

### Scramble als Production-Dependency

Scramble wird als `require` (nicht `require-dev`) installiert, weil `/docs/api.json` auf dem Produktionsserver erreichbar sein soll — für AI-Projekte, die die Spec direkt von der Live-URL konsumieren.

### Kein Commit der generierten Docs

Scramble generiert die Spec zur Laufzeit (on-request), nicht als statische Datei. Es gibt nichts zu committen — der Endpoint ist immer aktuell.

## Risks / Trade-offs

- **[Risk] Scramble erkennt nicht alle Response-Felder**: Wenn Eloquent Resources `toArray()` mit dynamischen Feldern verwenden → Mitigation: Scramble `@response`-Hints nur dort wo nötig
- **[Risk] Docs-Route öffentlich**: `/docs/api` ist ohne Auth erreichbar → kein Problem, da die API selbst auch public ist
