## Why

Die API hat nun eine OpenAPI-Spec für Menschen und HTTP-Clients — aber AI-Assistenten (z.B. Claude in einem Deck-Builder-Projekt) müssen trotzdem HTTP-Calls formulieren. Ein MCP-Server erlaubt direkten Datenzugriff ohne URL-Wissen, ohne HTTP-Overhead, und mit semantischen Tools wie "such alle roten Charaktere mit Cost 4".

## What Changes

- `laravel/mcp` installieren und einen MCP-Server in der Laravel-App konfigurieren
- 4 MCP-Tools erstellen, die direkt auf die Eloquent-Models zugreifen:
  - `list-packs` — alle Packs zurückgeben
  - `get-pack` — ein Pack mit seinen Karten (by ID)
  - `list-cards` — Karten mit optionalen Filtern (color, category, cost, pack_id, search)
  - `get-card` — eine einzelne Karte (by ID)
- MCP-Server unter `/mcp` erreichbar (HTTP SSE Transport)

## Capabilities

### New Capabilities

- `mcp`: MCP-Server mit Tools für direkten AI-Zugriff auf Packs und Karten

### Modified Capabilities

<!-- keine bestehenden Specs betroffen -->

## Impact

- Neue Composer-Dependency: `laravel/mcp`
- Neue Tool-Klassen unter `app/MCP/Tools/`
- MCP-Route unter `/mcp` (SSE)
- Kein Einfluss auf bestehende API-Routen unter `/api/v1/*`
- Kein Breaking Change

## Non-goals

- Authentifizierung des MCP-Endpoints (public, wie die REST API)
- Write-Operationen (nur lesend)
- Streaming/Subscriptions für Card-Updates
