## 1. Tests schreiben (TDD)

- [x] 1.1 Tests für neue API-Filter in `CardsEndpointTest`: name, rarity, attribute, type, pack label, cost_min/max, power_min/max, keyword, alt_art, combined filters
- [x] 1.2 Tests für Validierung (422 bei ungültigen Parametern: invalid rarity, non-numeric cost_min, per_page out of range)
- [x] 1.3 MCP Unit Tests für ListCardsTool: limit, neue Filter

## 2. Form Request

- [x] 2.1 `CardsIndexRequest` erstellen via `php artisan make:request CardsIndexRequest`
- [x] 2.2 Validierungsregeln für alle Parameter definieren (authorize true, rules für alle neuen + bestehenden Parameter)

## 3. API Implementation

- [x] 3.1 `CardsController::index` auf `CardsIndexRequest` umstellen
- [x] 3.2 Neue Filter-Chains hinzufügen: name, rarity, attribute, type, cost_min/max, power_min/max, keyword (mit Bracket-Wrapping), alt_art
- [x] 3.3 `pack` Filter auf Label-Join umstellen (JOIN packs ON pack_id = packs.id WHERE packs.label = ?)
- [x] 3.4 `per_page` Parameter für Pagination verwenden

## 4. MCP Tool Implementation

- [x] 4.1 `ListCardsTool` Schema: limit, name, rarity, attribute, type, pack_label, cost_min/max, power_min/max, keyword, alt_art hinzufügen
- [x] 4.2 `ListCardsTool` Handle: Filter-Chains + limit implementieren

## 5. Verification

- [x] 5.1 `php artisan test --compact` — alle Tests grün
- [x] 5.2 Pint formatieren
