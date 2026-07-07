# Laravel project template — four-agent AI team

A production-grade **Laravel 13 + PostgreSQL 17** starting template on the official
**React starter kit** (React 19 + Inertia 2 + TypeScript + Fortify auth), built to be
developed by a **four-agent Claude Code team** (CEO → Senior/Dev/Runner) with a token-cost-conscious
workflow. Everything runs in **Docker** (native engine); nothing installs on the host.

> **Humans start here → then `CLAUDE.md`.** For AI agents, `CLAUDE.md` is the entry
> point; it routes into `context/`, where every fact has exactly one home.

## What's inside

| Area | What you get |
|---|---|
| App | Laravel 13 (PHP 8.4) on the official React starter kit: React 19 + Inertia 2 + TypeScript + Tailwind 4 + shadcn/ui, **Fortify auth** (email verification enforced, 2FA + passkeys ready) |
| Environment | `docker compose up -d`: php-fpm, nginx with **HTTPS local vhost** (`https://examplesite.local` + subdomains), Postgres 17 (+ dedicated test DB), Redis, Vite node, Mailpit (:8025), queue worker + scheduler always on, browser-test service |
| Testing | Pest 4 (unit / feature / **browser**) against **real Postgres**: e2e, smoke group, accessibility assertions, cross-browser (Chrome/Firefox/WebKit), mobile-device emulation, staging/prod smoke via `E2E_BASE_URL`; arch tests, Larastan level 7, Pint + ESLint/Prettier/tsc — wired as the pre-merge gate and in CI |
| CI/CD | GitHub Actions CI (style/static/frontend/tests/assets, Gitleaks, Dependabot) + **VPS deploy pipeline**: GHCR images (php-fpm + Caddy auto-TLS) → SSH release with backup-gated migrations (`context/guides/deploy.md`) |
| Admin panel | **Filament v5** at `/admin`: Users management (list, approve, delete), optional registration-approval gate (`REQUIRE_ACCOUNT_APPROVAL`), panel MFA; first admin via `php artisan app:make-admin` |
| Agent team | CEO (specs, routing, reviews — no edits) → Senior/Dev/Runner (implement by complexity tier, one PR per task), coordinated through `context/agent_team/` mailbox files — see the charter |
| Guardrails | Claude Code hooks: block edits to `vendor/`/`storage/`/`public/build/`/`.env*`, warn on commit-to-main and on editing merged migrations |
| AI tooling | Laravel Boost MCP (dockerized) + Context7 in `.mcp.json`; frontend-design, Playwright, and official Laravel plugins auto-enabled (`context/guides/claude-tooling.md`) |
| Token/cost system | Thin-router docs + Laravel Boost MCP + Graphify knowledge graph (`context/token-optimization.md`) |

## Quick start (fresh clone)

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan test --testsuite=Unit,Feature   # green = healthy
```

App at <https://examplesite.local> (after the one-line `/etc/hosts` entry — see
`context/guides/docker.md`) or plain <http://localhost:8080>; mail at
<http://localhost:8025>.

## Starting a real project from this template

Follow `context/guides/new-project-from-template.md` — copy, re-identify, boot,
install the AI tooling (`boost:install`, `graphify install`), make the docs true,
launch the agent sessions (CEO + the agents you need).

## Documentation map

Everything lives in [`context/`](context/project-overview.md):
overview · [coding standards](context/coding-standards.md) ·
[workflow & agent team](context/ai-interaction.md) ·
[token policy](context/token-optimization.md) ·
[ADRs](context/decisions/README.md) · [guides](context/guides/) —
docker, testing, database, deploy, new-project.
