## 1. Write Tests (TDD)

- [x] 1.1 Tests for new API filters in `CardsEndpointTest`: name, rarity, attribute, type, pack label, cost_min/max, power_min/max, keyword, alt_art, combined filters
- [x] 1.2 Tests for validation (422 for invalid parameters: invalid rarity, non-numeric cost_min, per_page out of range)
- [x] 1.3 MCP unit tests for ListCardsTool: limit, new filters

## 2. Form Request

- [x] 2.1 Create `CardsIndexRequest` via `php artisan make:request CardsIndexRequest`
- [x] 2.2 Define validation rules for all parameters (authorize true, rules for all new + existing parameters)

## 3. API Implementation

- [x] 3.1 Switch `CardsController::index` to use `CardsIndexRequest`
- [x] 3.2 Add new filter chains: name, rarity, attribute, type, cost_min/max, power_min/max, keyword (with bracket wrapping), alt_art
- [x] 3.3 Switch `pack` filter to label join (JOIN packs ON pack_id = packs.id WHERE packs.label = ?)
- [x] 3.4 Use `per_page` parameter for pagination

## 4. MCP Tool Implementation

- [x] 4.1 `ListCardsTool` schema: add limit, name, rarity, attribute, type, pack_label, cost_min/max, power_min/max, keyword, alt_art
- [x] 4.2 `ListCardsTool` handle: implement filter chains + limit

## 5. Verification

- [x] 5.1 `php artisan test --compact` — all tests green
- [x] 5.2 Format with Pint
