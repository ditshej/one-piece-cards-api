# AI release review: project checklist

Points for this project, in addition to `docs/ai-review/base.md`.
A point marked "blocking" blocks the release like a blocking finding in the
base checklist.

- **Blocking:** REST (`CardsController`, `PacksController`) and MCP tools (`app/Mcp/Servers/CardsServer.php`) stay in sync; a change on one side without the other.
- **Blocking:** card filters live only in `Card::scopeApplyFilters()` / `Card::ARRAY_FILTER_PARAMS`; no filter logic in controllers or MCP tools.
- **Blocking:** tests never call the real `vega` binary; vegapull and SSH are faked with `Process::fake()`.
- **Blocking:** no hosts, users, server paths, tokens or real personal data in `CLAUDE.md`, `.ai/`, `docs/`, `.env*.example` or tests; placeholders and invented data only.
- Every Artisan command has `tests/Feature/Commands/<CommandName>Test.php`.
- The weekly import job in `routes/console.php` stays off in production.
