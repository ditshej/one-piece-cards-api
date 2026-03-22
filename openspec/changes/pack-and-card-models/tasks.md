## 1. Test Setup

- [ ] 1.1 Enable RefreshDatabase in tests/Pest.php (uncomment line 17)

## 2. Pack

- [ ] 2.1 Write Pack model tests (string PK, fillable attributes, hasMany cards relationship)
- [ ] 2.2 Create packs migration (string id PK, name string, timestamps, only up())
- [ ] 2.3 Create Pack model (string key, #[Fillable], cards() relationship)
- [ ] 2.4 Create PackFactory with realistic OPTCG pack data

## 3. Card

- [ ] 3.1 Write Card model tests (string PK, fillable attributes, belongsTo pack, JSON array casts, nullable fields)
- [ ] 3.2 Create cards migration (string id PK, pack_id FK, all attributes from spec, JSON columns, only up())
- [ ] 3.3 Create Card model (string key, #[Fillable], casts() for arrays, pack() relationship)
- [ ] 3.4 Create CardFactory with realistic OPTCG card data and automatic pack association

## 4. Verify

- [ ] 4.1 Run Pint to fix code style
- [ ] 4.2 Run full test suite and confirm all tests pass
