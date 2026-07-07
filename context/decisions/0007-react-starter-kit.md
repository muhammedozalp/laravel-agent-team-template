# 0007 — Official React starter kit (Inertia + Fortify) replaces Blade + Breeze

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

The template initially shipped plain Blade + Tailwind with Breeze v2 auth. Breeze
and Jetstream are in maintenance mode; Laravel's investment (passkeys, redesigned
Teams, first-party support) goes into the official starter kits — React, Vue,
Livewire — all built on **Fortify**, the headless auth backend. The owner wants the
template aligned with Laravel's official direction and chose the **React kit**
over the Livewire kit (the PHP-first alternative), accepting the tradeoff that
every feature now spans PHP + TSX and costs more AI-agent tokens per feature.

## Decision

Rebuild the app layer on `laravel/react-starter-kit` (**`dev-main`**, not the
stale Packagist stable — v1.0.x is the old Laravel 12, non-Fortify variant):
**React 19 + Inertia 2 + TypeScript + Tailwind 4 + shadcn/ui**, auth via
**Fortify**. The Breeze scaffolding (published Blade controllers/views and their
tests) is removed; git history keeps it. Infrastructure is unchanged: Docker
stack, PostgreSQL, `context/` system, hooks, CI shape, testing pyramid,
token-optimization stack.

SSR stays off by default (enable per project if SEO-critical pages need it).

## Consequences

- Auth logic lives in the Fortify package (config + wiring), not published
  controllers — upgrades come from `composer update`; passkeys/2FA are near.
- Frontend work is TypeScript/React: richer UI ceiling, second toolchain in the
  gate (eslint/prettier/tsc), higher token cost per UI feature.
- Context7 MCP becomes more valuable (React/Inertia/shadcn docs) — ADR 0005's
  "skip for now" is softened: enable it when frontend work starts.
- Browser tests target Inertia-rendered pages; assertions and flows updated.
- Until the kit tags a Laravel-13 stable release, the template tracks `dev-main`
  at scaffold time — pin the commit in `composer.lock` (committed) for
  reproducibility.
