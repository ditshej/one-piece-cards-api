## 1. Create MCP server & tools

- [x] 1.1 Create MCP server: `php artisan make:mcp-server CardsServer`
- [x] 1.2 Create tool: `php artisan make:mcp-tool ListPacksTool`
- [x] 1.3 Create tool: `php artisan make:mcp-tool GetPackTool`
- [x] 1.4 Create tool: `php artisan make:mcp-tool ListCardsTool`
- [x] 1.5 Create tool: `php artisan make:mcp-tool GetCardTool`

## 2. Implement tools

- [x] 2.1 `ListPacksTool`: return all packs via `Pack::orderBy('id')->get()`
- [x] 2.2 `GetPackTool`: pack with eager-loaded cards via `Pack::with('cards')->findOrFail($pack_id)`
- [x] 2.3 `ListCardsTool`: filter chain analogous to `CardsController@index` (color, category, cost, pack_id, search)
- [x] 2.4 `GetCardTool`: single card via `Card::findOrFail($card_id)`

## 3. Register & configure server

- [x] 3.1 Register tools in `CardsServer`
- [x] 3.2 Register server in `AppServiceProvider` or `config/mcp.php`

## 4. Tests

- [x] 4.1 Feature test: `list-packs` returns all packs
- [x] 4.2 Feature test: `get-pack` returns pack with cards
- [x] 4.3 Feature test: `list-cards` filters correctly by `color`
- [x] 4.4 Feature test: `get-card` returns a single card
