# Architecture Decision Records

_Append-only. Format: `NNNN-slug.md` with `# NNNN — title` / Date / Status /
Context / Decision / Consequences (template: `../templates/adr.md`). A superseded
ADR keeps its file; the new one links back._

| # | Decision | Status |
|---|---|---|
| [0001](0001-docker-native-dev.md) | All development runs in Docker (native engine); no host toolchain | Accepted |
| [0002](0002-postgresql.md) | PostgreSQL 17 as the database; tests run against Postgres, not SQLite | Accepted |
| [0003](0003-two-agent-team.md) | Two-agent team: Lead (orchestrates/reviews) + Dev (implements), files-as-mailbox | Superseded by 0008 |
| [0004](0004-testing-pyramid.md) | Pest testing pyramid (static → unit → feature → browser); Pint + Larastan gate | Accepted |
| [0005](0005-token-optimization-stack.md) | Token stack: native discipline + Laravel Boost MCP + Graphify | Accepted |
| [0006](0006-tdd-workflow.md) | TDD (red → green → refactor) is the default development approach | Accepted |
| [0007](0007-react-starter-kit.md) | Official React starter kit (Inertia + Fortify) replaces Blade + Breeze | Accepted |
| [0008](0008-four-agent-team.md) | Four-agent team: CEO + Senior/Dev/Runner, routing by complexity | Accepted |
| [0009](0009-filament-admin.md) | Filament v5 is the standard admin panel (users list/approve/delete, approval gate) | Accepted |
