# 0002 — PostgreSQL 17; tests run against Postgres, not SQLite

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

The owner standardizes on PostgreSQL for production projects. Laravel's default test
setup uses in-memory SQLite, which is fast but silently diverges from production
(JSONB, ILIKE, sequences, constraint behavior, full-text search).

## Decision

PostgreSQL 17 everywhere: dev, tests, CI. The `db` container provisions two
databases (`app`, `app_testing`); `phpunit.xml` points at `app_testing` with
`RefreshDatabase`. CI runs a `postgres:17` service so the suite is identical.

## Consequences

- Tests exercise the real dialect — Postgres-specific features are safe to use and
  are covered (see `../guides/database.md`).
- Suite is somewhat slower than SQLite-in-memory; acceptable at template scale, and
  parallel Pest (`--parallel`) recovers most of it later.
- The test DB is wiped by `RefreshDatabase` — never point `phpunit.xml` at the dev DB.
