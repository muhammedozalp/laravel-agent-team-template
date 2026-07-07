# CLAUDE.md

_**Thin router for agents.** The full project docs live in **`context/`** — this file
only orients you and points there so every fact has one home
(`context/project-overview.md` owns what/stack/architecture; this file links, never
restates). **Use it** as the entry point, then read the linked `context/` doc._

**Full-stack Laravel project template** — a reusable starting point for production
Laravel websites. **Laravel 13 (official React starter kit: React 19 + Inertia 2 +
TypeScript + Fortify auth) + PostgreSQL**, running entirely inside **Docker (native
engine)** behind a local **HTTPS vhost** (`https://examplesite.local`), developed by
a **4-agent team** (CEO → Senior/Dev/Runner), with multi-layer testing (unit/feature/browser —
e2e, smoke, a11y, cross-browser/device), CI/CD, and a token/cost-conscious workflow.

> **This is a template.** When starting a real project from it, run the bootstrap
> steps in `context/guides/new-project-from-template.md` (rename, env, git remote).

## Start here → `context/`

| You need… | Read |
|---|---|
| What it is + stack + architecture (+ full router) | `context/project-overview.md` |
| Conventions — naming / env / code style / lint | `context/coding-standards.md` |
| How we ship — workflow, agent team, pre-merge gate | `context/ai-interaction.md` |
| Where we are now + history | `context/current-feature.md` |
| Why a choice was made (ADRs) | `context/decisions/` |
| Feature / fix specs (work + status) | `context/feature/` · `context/fix/` |
| Undecided ideas / discussions | `context/backlog/` |
| Agent coordination (CEO → Senior/Dev/Runner) | `context/agent_team/index.md` (charter; logs + board are local) |
| Token / context / session cost policy | `context/token-optimization.md` |
| Docker environment how-to | `context/guides/docker.md` |
| Auth: what ships, hardening, deliberate absences | `context/guides/auth.md` |
| Testing strategy (unit / feature / browser / static) | `context/guides/testing.md` |
| Database & PostgreSQL practices | `context/guides/database.md` |
| Deploy / CI-CD how-to | `context/guides/deploy.md` |
| Start a new project from this template | `context/guides/new-project-from-template.md` |

## Commands

```bash
# All app commands run inside Docker — never on the host.
docker compose up -d                  # start the full stack (app, postgres, redis, mailpit, node)
docker compose exec app php artisan   # artisan entry point
docker compose exec app composer      # composer entry point
docker compose exec app php artisan test              # full test suite (Pest)
docker compose run --rm browser php artisan test --testsuite=Browser   # e2e/smoke/a11y
docker compose exec app ./vendor/bin/pint             # code style (Laravel Pint)
docker compose exec app ./vendor/bin/phpstan analyse  # static analysis (Larastan)
docker compose exec node npm run dev                  # Vite dev server
docker compose exec node npm run build                # production asset build
docker compose exec node npm run lint:check           # ESLint (frontend gate)
docker compose exec node npm run types:check          # TypeScript check (frontend gate)
```

## Non-negotiables (detail in `context/`)

- **Git:** PR → `main`; **never commit to `main`**; **never delete branches**;
  milestone tags only. (`context/ai-interaction.md`)
- **Never edit** `vendor/`, `node_modules/`, `public/build/`, or `.env*`; **`.env` is
  never committed** (only `.env.example`). Guardrail hooks enforce this.
  (`context/coding-standards.md`)
- **Everything runs in Docker** — no host PHP/composer/node/psql. (`context/guides/docker.md`)
- **Migrations are append-only** once merged — never edit a merged migration; write a
  new one. (`context/guides/database.md`)
- **Tests gate every merge** — Pint + PHPStan + Pest must pass locally and in CI
  before a PR is merged. (`context/guides/testing.md`)
- **Test-first (TDD):** production code exists to make a failing test pass —
  red → green → refactor; exemptions are declared in the REPORT.
  (`context/guides/testing.md` § TDD, ADR 0006)
- **Keep docs in sync:** a behavior change updates the affected docs **in the same
  PR.** (`context/ai-interaction.md`)
- **Mind the token budget:** follow `context/token-optimization.md` — read only the
  `context/` doc you need, prefer skills/subagents over pasting large files, and use
  the configured MCP servers (e.g. **Context7** for library docs) instead of guessing.
