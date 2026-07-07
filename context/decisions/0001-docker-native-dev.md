# 0001 — All development runs in Docker (native engine)

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

The template must reproduce identically across machines and projects. Host-installed
PHP/Composer/Node/psql versions drift, and Laravel Sail (the official wrapper) hides
the compose file behind a vendor script, which makes per-project tuning and CI parity
less transparent.

## Decision

Plain `docker compose` with our own `docker/` build files — services: `app`
(php-fpm 8.4), `web` (nginx), `db` (postgres:17-alpine), `redis`, `node` (Vite),
`mailpit`, and an optional `queue` worker. No PHP/Node/psql on the host, ever;
`CLAUDE.md` commands are all `docker compose exec …`. Sail is not used, but the
compose file follows its service naming so Sail-based docs still map.

## Consequences

- One-command onboarding (`docker compose up -d`), identical env in CI.
- Guardrail permission allowlists can safely include `docker compose exec app …`.
- Slight overhead: file-sync performance on non-Linux hosts; commands are longer
  (mitigate with shell aliases if desired — not committed, host-specific).
