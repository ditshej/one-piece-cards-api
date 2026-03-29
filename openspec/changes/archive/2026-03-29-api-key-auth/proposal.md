## Why

Die API und der MCP-Server sind öffentlich zugänglich. Da sie auf privatem Hosting läuft, können Dritte sie nutzen und Traffic/Kosten verursachen. Das Projekt soll open-source werden, aber jeder soll seine eigene Instanz betreiben — nicht die des Autors.

## What Changes

- `ApiKeyMiddleware` schützt alle `/api/v1/*`-Routes und den MCP-Endpoint `/mcp`
- API-Key wird als statischer Wert in `.env` (`API_KEY`) konfiguriert
- Requests müssen `Authorization: Bearer <key>` Header senden
- Requests ohne oder mit falschem Key erhalten 401
- Scramble-Docs (`/docs/api*`) bleiben öffentlich — sie geben keine Daten zurück

## Capabilities

### New Capabilities

- `api-key-auth`: Statische API-Key-Authentifizierung für REST-API und MCP-Server

### Modified Capabilities

<!-- keine bestehenden Specs betroffen -->

## Impact

- Alle bestehenden API-Tests müssen den Auth-Header senden
- MCP-Integration in anderen Projekten braucht `Authorization: Bearer <key>` Header
- Kein Breaking Change für den MCP-Server (Header-Support ist in `laravel/mcp` eingebaut)
- Keine Datenbankänderungen, kein neues Package

## Non-goals

- Per-User-Token-Management (kein Sanctum/Passport)
- Rate Limiting
- Token-Rotation oder Expiry
- Schutz der Scramble-Docs
