# Idea: make the MCP server usable from ChatGPT

**Status:** idea, not scheduled · **Investigated:** 2026-09-20 · **Not an OpenSpec change yet**

This records an investigation so it does not have to be repeated. Nothing here is
implemented. If this ever gets picked up, it starts with `/opsx:propose` like any
other change.

## The question

Can `https://op-cards.ditshej.ch/mcp` be used from ChatGPT, the way it already is from
Claude Code?

## What was verified against production

The endpoint is healthy and needs no changes for a static-token client:

| Probe | Result |
|---|---|
| `initialize` with `Authorization: Bearer <sanctum-token>` | `200`, protocol `2025-06-18` |
| `tools/list`, same token, no `Mcp-Session-Id` | `200`, all four tools |
| `Accept: application/json` only, and no `Accept` at all | `200` — no strict streaming requirement |
| Unauthenticated, with `Accept: application/json` | `401`, `WWW-Authenticate: Bearer realm="mcp", error="invalid_token"` |
| `/.well-known/oauth-protected-resource` | `404` — no OAuth discovery |

Current exposure, for the record: `/api/v1/*` and `POST /mcp` both require a token;
only `/`, `/up`, `/docs/api` and `/docs/api.json` are public, and the docs contain no
card data. This matches `openspec/specs/api-key-auth/spec.md`.

## The two integration paths

ChatGPT reaches an MCP server in two different ways, and they authenticate differently:

```
  Path A — OpenAI Responses / Agents API
      caller's own script
          -> tools: [{ type: "mcp", server_url, authorization, require_approval }]
          -> POST /mcp with Authorization: Bearer <static token>
      WORKS TODAY. No server change.
      But: only from code you run yourself.

  Path B — custom connector inside the ChatGPT app
      ChatGPT chat
          -> connector (Streamable HTTP)
          -> OAuth 2.1 only, or no authentication at all
      Needed if the goal is calling the tools from inside a normal ChatGPT conversation.
```

**The decisive constraint is the runtime, not the authentication.** A ChatGPT chat has no
freely configurable MCP client — it can only use tools its environment already provides.
So Path A cannot be driven from inside a chat, no matter how the server authenticates.
ChatGPT's own agent confirmed this when asked directly, and also confirmed that its
earlier claim that Path A "does not work" came from conflating the two paths, not from a
failed request against this server.

## What Path B would cost

Roughly **four hours of focused work, plus an unpredictable final step** — not the
"straightforward" it looked like at first glance.

| # | Step | Estimate |
|---|---|---|
| 1 | Install Passport, migrations, keys locally | 15 min |
| 2 | OAuth model on the `users` table + provider + `api` guard | 20 min |
| 3 | `Mcp::oauthRoutes()` + `auth:api,sanctum` on `/mcp` | 10 min |
| 4 | Login route + view + password command | 45–60 min |
| 5 | `Passport::skipAuthorization()` | 10 min |
| 6 | Secure client registration: throttle + `redirect_domains` | 20 min |
| 7 | Tests: OAuth 401/200, dual guard, login, DCR | 45–60 min |
| 8 | Deploy: keys on shared hosting, `chmod 600`, `_deploy.sh` step | 30 min |
| 9 | Docs: README, landing page, Scramble description | 20 min |
| 10 | OpenSpec change, propose through archive | 40 min |
| 11 | First real handshake with ChatGPT | 15 min – 2 h |

### The four findings that drive that estimate

1. **Passport and Sanctum cannot share the `User` model.** Passport's `OAuthenticatable`
   interface requires `createToken(string $name, array $scopes = []): PersonalAccessTokenResult`;
   Sanctum's trait supplies an incompatible signature, plus five colliding methods and one
   colliding property (`app/Models/User.php:11,18`). A second Eloquent model pointing at the
   same `users` table, implementing Passport's contract, resolves this without duplicating
   user records.

2. **A dual guard keeps everything working.** `->middleware('auth:api,sanctum')` on the
   existing `/mcp` route — Laravel's `Authenticate` middleware tries each guard in turn
   (`vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:81-87`).
   ChatGPT authenticates via OAuth, the Sanctum token in `~/.claude.json` keeps working,
   one URL, nothing breaks. The `api-key-auth` spec also stays true in substance: the
   endpoint still requires authentication, just by a second mechanism.

3. **The app has no login, and the authorization-code flow needs one.** A human must sign
   in and consent in a browser. There is no login route, and `token:create` issues
   password-less users (`TokenCreate.php:19-23`). This is the largest single item.
   `Passport::skipAuthorization()` removes the consent screen and with it the published
   `mcp/authorize.blade.php`, which expects Vite assets and shadcn colour tokens that
   `resources/css/app.css` (11 lines) does not define — worth doing, saves an hour of
   styling.

4. **`Mcp::oauthRoutes()` registers `POST /oauth/register` with no middleware at all**
   (`vendor/laravel/mcp/src/Server/Registrar.php:109`), and `config('mcp.redirect_domains')`
   defaults to `['*']`. An open, unthrottled dynamic-client-registration endpoint. Both must
   be tightened before this goes anywhere near production.

### Risks

- **Step 11 is genuinely unpredictable.** Whether ChatGPT's client accepts our discovery
  documents, completes dynamic client registration and survives the popup flow is unknown
  until tried. Laravel MCP sets `authorization_servers` to the *path-scoped* URL rather than
  the application root (`Registrar.php:94`); a client resolving strictly per RFC 8414 would
  404. Path-insertion discovery, as ChatGPT and Claude use, does work.
- **New dependencies need approval.** Passport pulls in `league/oauth2-server`,
  `firebase/php-jwt`, `phpseclib` and `php-http/discovery`. Passport v13.8 supports
  `illuminate/* ^13.0`, so Laravel 13 is fine.
- **Keys on shared hosting.** `passport:keys` must be generated on the server, kept out of
  git, set to `chmod 600`, and `_deploy.sh` has no step for it today.

### Suggested de-risking, if this is ever picked up

Do not build it in order. Spend ~90 minutes on steps 1, 2, 3, 5 plus a throwaway login on a
branch, deploy nothing, then drive the full OAuth flow with `php artisan mcp:inspector mcp`
— the inspector is a real MCP client and performs the whole dance. If the handshake works,
the metadata is sound and the remaining ~2.5 hours are routine. If it fails, 90 minutes are
gone instead of four hours, and the reason is known.

## Open decisions

- **Login mechanism** — a minimal custom login route (~100 lines, no new dependency, fits
  the app's lean shape) versus Laravel Breeze (brings registration, password reset and
  profile that nothing here needs). Deliberately left open.
- **Whether to do this at all.** Path A already works for programmatic use. Path B buys
  exactly one thing: calling the tools from inside normal ChatGPT conversations.

## Rejected, and why

**A public, unauthenticated MCP route with rate limiting.** ChatGPT connectors do support
"no authentication", and the data is public Bandai card data served read-only, so the
protection goal would be load rather than confidentiality. Rejected because
`openspec/specs/api-key-auth/spec.md:18-19` deliberately specifies token-gated access and
the public API description tells consumers that "tokens are issued per consuming
application". Reversing that is a product decision, not a shortcut to take in passing. If it
is ever wanted, it should be its own OpenSpec change that amends the spec and records the
reasoning — and without an unguessable URL slug, which would only simulate protection.

## Unrelated bugs found along the way

- **Unauthenticated requests without `Accept: application/json` return 500 instead of 401.**
  Laravel redirects unauthenticated non-JSON requests to a `login` route that does not exist.
  Reproduced locally: `Route [login] not defined`. A browser hitting the API sees a server
  error rather than a clean 401. Small, self-contained fix.
- **`list-cards-tool` with `limit=100` returns roughly 63 KB** and overflows a typical tool
  result budget. A leaner projection or a lower default limit would help any model.
