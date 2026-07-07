# Database — PostgreSQL practices

_PostgreSQL 17 in all environments (ADR `../decisions/0002-postgresql.md`). Dev DB
`app`, test DB `app_testing` (wiped by every test run — never point tests at `app`)._

## Migrations

- **Append-only once merged:** never edit a merged migration — write a new one.
  Editing history breaks every environment that already ran it.
- Every migration must have a working `down()` (or be explicitly irreversible with
  a comment saying why).
- Data migrations (backfills) are separate from schema migrations and idempotent.
- Name indexes/constraints explicitly when non-trivial — Postgres auto-names are
  hostile to later `DROP`s.

## Schema conventions

- `snake_case` plural tables, `id` bigint identity (Laravel default), `timestampsTz()`
  over `timestamps()` (Postgres does real timezones — use them).
- Foreign keys always declared (`foreignId(...)->constrained()->cascadeOnDelete()` or
  explicit restrict) — integrity lives in the DB, not only in Eloquent.
- Use Postgres strengths deliberately: `jsonb` for ragged attributes (with GIN index
  when queried), partial/expression indexes, `citext` or `->unique()` on
  lower-cased expression for case-insensitive uniques, full-text search before
  reaching for external search services.
- Enums: prefer string columns + PHP backed enums (casts) over native Postgres enums
  (native enums make additive migrations awkward).

## Query discipline

- N+1s are bugs: eager-load (`with()`), and enable
  `Model::shouldBeStrict()` in non-production (`AppServiceProvider`) so lazy loads
  throw in dev/tests.
- Heavy reads get `select()`ed columns and chunking (`lazyById`) — no unbounded
  `all()`.
- Raw SQL is allowed where Eloquent obscures intent — parameterized always, and
  covered by a feature test against real Postgres.

## Operations

- Dev psql/dumps: `docker compose exec db pg_dump -U app app > …`.
- **Production backups** are automatic (nightly rotated dumps + one before every
  migrate — `deploy.md` § Backups). **Restore** on the VPS:
  ```bash
  docker compose -f docker-compose.prod.yml stop app queue scheduler
  ls /var/lib/docker/volumes/app-prod_db-backups/_data/daily/   # pick a dump
  zcat <dump>.sql.gz | docker compose -f docker-compose.prod.yml exec -T db \
      psql -U app -d app
  docker compose -f docker-compose.prod.yml start app queue scheduler
  ```
- Schema questions in an AI session go to **Boost's schema tool or Graphify's graph**
  first (`../token-optimization.md`) — not to reading every migration file.
