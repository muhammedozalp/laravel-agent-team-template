# Project overview

_Canonical "what it is + stack + architecture". Other docs link here — this file owns
these facts; nothing below is restated elsewhere._

## What it is

A **production-grade full-stack Laravel project template** developed by a 4-agent
Claude Code team (CEO → Senior/Dev/Runner, ADR 0008). It is meant to be cloned as the starting point for new
client websites: everything a real project needs on day one — Docker environment,
database, testing pyramid, CI, agent workflow, guardrails, and a token-cost policy —
is already wired.

When this template becomes a real project, replace this section with the actual
product description (see `guides/new-project-from-template.md`).

## Stack

| Layer | Choice | Why (ADR) |
|---|---|---|
| Framework | **Laravel 13** (PHP 8.4), official **React starter kit** (`dev-main`) | `decisions/0007-react-starter-kit.md` |
| Database | **PostgreSQL 17** | `decisions/0002-postgresql.md` |
| Cache / queue | **Redis** | Laravel-native driver, one container |
| Frontend | **React 19 + Inertia 2 + TypeScript + Tailwind 4 + shadcn/ui**; Wayfinder generates typed routes | `decisions/0007-react-starter-kit.md` |
| Auth | **Fortify** — registration, login, password reset, **enforced email verification**, 2FA, passkeys, settings pages | `config/fortify.php`; tested in `tests/Feature/Auth` + `tests/Browser` |
| Admin | **Filament v5** at `/admin` — Users resource (list/approve/delete), optional approval gate, panel MFA | `decisions/0009-filament-admin.md`; `guides/auth.md` § Admin panel |
| Runtime | **Docker native engine** — app (php-fpm) + nginx + postgres + redis + node + mailpit | `decisions/0001-docker-native-dev.md` |
| Tests | **Pest** (unit / feature / browser) + **Larastan** (static) + **Pint** (style) | `decisions/0004-testing-pyramid.md` |
| Mail (dev) | Mailpit (catches all outbound mail, UI on :8025) | no accidental real mail |
| AI tooling | Laravel Boost MCP · Graphify · thin-router docs | `decisions/0005-token-optimization-stack.md` |

## Architecture

Standard Laravel skeleton at the repo root:

- `app/` — domain code. Controllers stay thin (return `Inertia::render(...)`);
  business logic in `app/Actions/` or `app/Services/`; queries in models/scopes.
  Fortify wiring: `app/Providers/FortifyServiceProvider.php` + `app/Actions/Fortify/`.
- `routes/` — `web.php` (Inertia pages), `settings.php` (profile/password/2FA),
  `console.php` (scheduled commands). Auth routes come from Fortify.
- `database/migrations/` — append-only once merged (see `guides/database.md`).
- `resources/js/` — the React app: `pages/` (Inertia pages), `components/`
  (shadcn/ui + app components), `layouts/`, `hooks/`, `types/`. `actions/`,
  `routes/`, `wayfinder/` are **generated** by Wayfinder — never edit.
- `tests/` — `Unit/`, `Feature/`, `Browser/` (see `guides/testing.md`).
- `docker/` + `docker-compose.yml` — the whole environment (see `guides/docker.md`).
- `public/build/` — Vite output; **generated, never edited**.

Request path (dev): browser → nginx `:8080` → php-fpm (`app` container) → postgres /
redis. Vite dev server (`node` container, `:5173`) serves hot assets.

## Where the rest lives (router)

| Topic | Doc |
|---|---|
| Conventions — naming / env / code style / lint | `coding-standards.md` |
| How we ship — workflow, agent team, pre-merge gate | `ai-interaction.md` |
| Where we are now + history | `current-feature.md` |
| Token / context / session cost policy | `token-optimization.md` |
| Why a choice was made | `decisions/` (ADRs, `README.md` = index) |
| Work items (specs = status board) | `feature/` · `fix/` |
| Undecided ideas | `backlog/` (`README.md` = lifecycle) |
| Spec / ADR file templates | `templates/` |
| Agent coordination (CEO → Senior/Dev/Runner) | `agent_team/index.md` |
| Docker how-to | `guides/docker.md` |
| Auth map + admin panel + deliberate absences | `guides/auth.md` |
| Claude Code tooling (plugins / MCPs / skills) | `guides/claude-tooling.md` |
| Testing strategy | `guides/testing.md` |
| PostgreSQL practices | `guides/database.md` |
| Deploy / CI-CD | `guides/deploy.md` |
| Bootstrap a new project from this template | `guides/new-project-from-template.md` |
