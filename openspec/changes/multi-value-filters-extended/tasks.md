## 1. Tests (TDD — write first)

- [ ] 1.1 Add test: `?color[]=Red&color[]=Yellow` returns cards with Red OR Yellow
- [ ] 1.2 Add test: `?rarity[]=SR&rarity[]=SEC` returns cards with rarity SR or SEC
- [ ] 1.3 Add test: `?card_set[]=OP13&card_set[]=OP15` returns cards from either set
- [ ] 1.4 Add test: `?type[]=Minks&type[]=Strawhats` returns cards with Minks OR Strawhats type
- [ ] 1.5 Add test: `?keyword[]=Blocker&keyword[]=Rush` returns cards with [Blocker] OR [Rush]
- [ ] 1.6 Add test: `?power[]=8000&power[]=10000` returns cards with power 8000 or 10000
- [ ] 1.7 Add test: `?power[]=abc` returns 422

## 2. Implementation

- [ ] 2.1 Extend `prepareForValidation()` in `CardsIndexRequest` to normalise all 6 new params (power, color, rarity, card_set, type, keyword)
- [ ] 2.2 Update validation rules for all 6 params to accept arrays
- [ ] 2.3 Update `color` filter in `scopeApplyFilters` — orWhereJsonContains loop
- [ ] 2.4 Update `rarity` filter — whereIn
- [ ] 2.5 Update `card_set` filter — whereIn
- [ ] 2.6 Update `type` filter — orWhereJsonContains loop
- [ ] 2.7 Update `keyword` filter — orWhere LIKE loop over effect + trigger
- [ ] 2.8 Add `power` exact-match filter — whereIn
