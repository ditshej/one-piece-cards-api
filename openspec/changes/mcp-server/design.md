## Context

`laravel/mcp` ist bereits via `laravel/boost` installiert (v0.6.4). Die Artisan-Commands `make:mcp-server` und `make:mcp-tool` sind verfügbar. Die bestehenden Eloquent-Models (`Pack`, `Card`) und die Filterlogik aus `CardsController` können direkt wiederverwendet werden.

## Goals / Non-Goals

**Goals:**
- MCP-Server mit 4 Tools: `list-packs`, `get-pack`, `list-cards`, `get-card`
- Direkter Eloquent-Zugriff (kein HTTP-Overhead)
- Tools geben dieselben Daten zurück wie die REST API

**Non-Goals:**
- Eigene `laravel/mcp` Installation (bereits via boost)
- Auth/Middleware für den MCP-Endpoint
- Write-Tools

## Decisions

### Direkte Eloquent-Queries statt HTTP-Calls

Die MCP-Tools greifen direkt auf die Models zu — kein interner HTTP-Call zur REST API. Das ist effizienter und vermeidet Zirkularität.

### Filterlogik aus CardsController wiederverwenden

`list-cards` implementiert dieselbe `->when()`-Filterkette wie `CardsController@index`. Kein eigener Service nötig — die Logik ist einfach genug für direkten Code.

### Tool-Struktur

Ein MCP-Server (`CardsServer`) mit 4 registrierten Tools. Jedes Tool in eigenem File unter `app/MCP/Tools/`. Server registrieren via `McpServiceProvider` oder direkt in `AppServiceProvider`.

### Return-Format

Tools geben Arrays zurück (kein JSON-String). `laravel/mcp` serialisiert automatisch. Felder identisch mit den Eloquent Resources der REST API.

## Risks / Trade-offs

- **[Risk] laravel/mcp API ändert sich**: Paket ist v0.x — Breaking Changes möglich → Mitigation: Version in composer.json pinnen
- **[Risk] N+1 bei `list-packs` mit Karten**: `get-pack` lädt Karten eager, `list-packs` ohne → kein Problem, da `list-packs` keine Karten zurückgibt
