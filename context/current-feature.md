# Current feature — where we are now

_Single committed dashboard: active work, known issues, and the milestone history.
Updated at milestones, not per step. The `feature/` and `fix/` folders ARE the
detailed status board (each spec carries its own `Status:` line)._

## Active

Queued: a concrete VPS deploy runbook + production Docker build (owner decision
2026-07-07; replaces the options section in `guides/deploy.md`).

> **Template-creation mode:** while the template itself is being built, work lands
> directly on `main` and no feature/fix spec files are created — the spec system
> and the branch→PR→review lifecycle in `ai-interaction.md` are FOR real projects
> started from the template. The template's own history lives in git and §History.

## Status pointer

Open work lives in `context/feature/` and `context/fix/` — check each spec's
`Status:` line. Undecided ideas: `context/backlog/`.

## Known issues

- None yet.

## History (newest first)

- **2026-07-07** — **Filament v5 standard admin (ADR 0009):** `/admin` panel with
  Users resource (list/approve/delete), optional approval gate
  (`REQUIRE_ACCOUNT_APPROVAL`), panel MFA, `app:make-admin` command — all TDD'd
  (16 new tests). Claude tooling wired: dockerized Boost MCP, frontend-design/
  Playwright/official-Laravel plugins auto-enabled, `guides/claude-tooling.md`.

- **2026-07-07** — **4-agent team (ADR 0008):** expanded Lead+Dev to the reference
  project's CEO → Senior/Dev/Runner model — routing by complexity, per-agent logs,
  same star topology and serialization. Hardening batch shipped the same day:
  session invalidation on password change, email-change gate + old-address alert,
  register throttling, auth audit log, queued auth mail, security headers,
  always-on queue+scheduler, Sentry slot.

- **2026-07-07** — **Stack pivot (ADR 0007):** rebuilt on the official React
  starter kit (`dev-main`): React 19 + Inertia 2 + TypeScript + shadcn/ui,
  **Fortify** auth (registration, reset, enforced email verification, 2FA,
  passkeys). PHPStan raised to level 7; ESLint/Prettier/tsc join the gate; node
  service now PHP+Node (Wayfinder). Kit a11y defects fixed (positive tabIndex,
  welcome contrast). 44 feature + 9 browser tests green. Supersedes the Breeze
  Blade auth (kept in git history).

- **2026-07-07** — Auth added: Breeze v2 Blade stack on the Tailwind 4 toolchain,
  enforced email verification (`MustVerifyEmail`), a11y fixes to scaffolding,
  30 feature + 9 browser tests green. *(Superseded by the React-kit pivot.)*

- **2026-07-07** — Template created: Laravel 13 + PostgreSQL 17 Docker stack,
  `context/` doc system, Lead+Dev agent team, Pest/Larastan/Pint gate, CI workflow,
  guardrail hooks, token-optimization stack (Boost + Graphify + Context7-optional).
