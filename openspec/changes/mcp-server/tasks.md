## 1. MCP Server & Tools erstellen

- [x] 1.1 MCP-Server erstellen: `php artisan make:mcp-server CardsServer`
- [x] 1.2 Tool erstellen: `php artisan make:mcp-tool ListPacksTool`
- [x] 1.3 Tool erstellen: `php artisan make:mcp-tool GetPackTool`
- [x] 1.4 Tool erstellen: `php artisan make:mcp-tool ListCardsTool`
- [x] 1.5 Tool erstellen: `php artisan make:mcp-tool GetCardTool`

## 2. Tools implementieren

- [x] 2.1 `ListPacksTool`: alle Packs via `Pack::orderBy('id')->get()` zurückgeben
- [x] 2.2 `GetPackTool`: Pack mit eager-loaded Cards via `Pack::with('cards')->findOrFail($pack_id)`
- [x] 2.3 `ListCardsTool`: Filterkette analog zu `CardsController@index` (color, category, cost, pack_id, search)
- [x] 2.4 `GetCardTool`: einzelne Karte via `Card::findOrFail($card_id)`

## 3. Server registrieren & konfigurieren

- [x] 3.1 Tools in `CardsServer` registrieren
- [x] 3.2 Server in `AppServiceProvider` oder `config/mcp.php` registrieren

## 4. Tests

- [x] 4.1 Feature-Test: `list-packs` gibt alle Packs zurück
- [x] 4.2 Feature-Test: `get-pack` gibt Pack mit Karten zurück
- [x] 4.3 Feature-Test: `list-cards` filtert korrekt nach `color`
- [x] 4.4 Feature-Test: `get-card` gibt eine Karte zurück
