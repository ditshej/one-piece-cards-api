## 1. Artisan Command

- [x] 1.1 Create `app/Console/Commands/TokenList.php`: signature `token:list {--json}`, query `PersonalAccessToken::with('tokenable:id,email')->orderByDesc('created_at')->get(...)`, table output with columns `ID`, `Name`, `User`, `Last used`, `Created`; handle null tokenable (orphan tokens); `--json` flag outputs `json_encode`; empty list shows info message and returns `SUCCESS`

## 2. Tests

- [x] 2.1 Create `tests/Feature/Commands/TokenListTest.php` (TDD — write before implementing):
  - `it('lists all tokens with name, user email, last used and created at')` — two tokens via factory + `createToken()`, assert table output contains name and email
  - `it('shows a friendly message when no tokens exist')` — empty DB, assert info output and exit `SUCCESS`
  - `it('outputs json when --json flag is passed')` — one token, assert output is valid JSON containing name and email

## 3. Shell Wrapper

- [x] 3.1 Add `--list` branch to `create-token.sh` (after the `--revoke` block, before the argument-count check): SSH into server and run `php artisan token:list`
- [x] 3.2 Update usage text in `create-token.sh` to include `./create-token.sh --list`

## 4. Documentation

- [x] 4.1 Add `token:list` and `./create-token.sh --list` usage to `README.md` Token Management section (after the revoke examples)

## 5. Spec Update

- [x] 5.1 Add listing requirements and scenarios to `openspec/specs/token-management/spec.md`

## 6. Cleanup

- [x] 6.1 Run `vendor/bin/pint --dirty --format agent`
- [x] 6.2 Run `php artisan test --compact --filter=Token` — all token tests must pass
