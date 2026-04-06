## 1. Tests (TDD — write first)

- [x] 1.1 Add test: `?color[]=Red&color[]=Yellow` returns cards with Red OR Yellow
- [x] 1.2 Add test: `?rarity[]=SR&rarity[]=SEC` returns cards with rarity SR or SEC
- [x] 1.3 Add test: `?card_set[]=OP13&card_set[]=OP15` returns cards from either set
- [x] 1.4 Add test: `?category[]=Character&category[]=Leader` returns cards with either category
- [x] 1.5 Add test: `?category[]=Invalid` returns 422
- [x] 1.6 Add test: `?type[]=Minks&type[]=Strawhats` returns cards with Minks OR Strawhats type
- [x] 1.7 Add test: `?attribute[]=Wisdom&attribute[]=Strike` returns cards with Wisdom OR Strike attribute
- [x] 1.8 Add test: `?keyword[]=Blocker&keyword[]=Rush` returns cards with [Blocker] OR [Rush]
- [x] 1.9 Add test: `?power[]=8000&power[]=10000` returns cards with power 8000 or 10000
- [x] 1.10 Add test: `?power[]=abc` returns 422

## 2. Implementation

- [x] 2.1 Extend `prepareForValidation()` in `CardsIndexRequest` to normalise all 8 new params (power, color, rarity, card_set, category, type, attribute, keyword)
- [x] 2.2 Update validation rules for all 8 params to accept arrays
- [x] 2.3 Update `color` filter in `scopeApplyFilters` — orWhereJsonContains loop
- [x] 2.4 Update `rarity` filter — whereIn
- [x] 2.5 Update `card_set` filter — whereIn
- [x] 2.6 Update `category` filter — whereIn
- [x] 2.7 Update `type` filter — orWhereJsonContains loop
- [x] 2.8 Update `attribute` filter — orWhereJsonContains loop
- [x] 2.9 Update `keyword` filter — orWhere LIKE loop over effect + trigger
- [x] 2.10 Add `power` exact-match filter — whereIn
