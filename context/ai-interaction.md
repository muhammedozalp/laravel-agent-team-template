# AI interaction — how we ship

_Canonical workflow reference: the agent team, task lifecycle, pre-merge gate, and
git/process rules. The team charter (roles, communication protocol) lives in
`agent_team/index.md` — this file owns the **process** every agent follows._

## The team (summary — charter is `agent_team/index.md`)

Four Claude Code sessions plus the human owner (ADR 0008):

| Who | Launched from | Does |
|---|---|---|
| **CEO** | parent workspace (above the repo) | orchestrates: writes specs & tasks, assigns by complexity, reviews every PR read-only (`gh pr diff`). **No hands-on edits.** |
| **Senior** | repo root (hooks + permissions load) | hard / risky / architectural tasks |
| **Dev** | repo root (hooks + permissions load) | well-specified features — the default assignee |
| **Runner** | repo root (hooks + permissions load) | mechanical, bounded chores |
| **Owner** (human) | — | sets priorities, relays messages between sessions, merges PRs |

## Task lifecycle — one PR per task

1. **Spec first.** Every task starts as `context/feature/NN-slug.md` or
   `context/fix/NN-slug.md` (templates in `context/templates/`). The spec is the
   source of truth: Goal, Steps checklist, Notes/decisions, Resolution.
2. **CEO assigns** in the chosen agent's `agent_team/log-<role>.md`: a `### N.M CEO · TASK` entry naming
   the spec and the exact branch `iNNN/<slug>`.
3. **The agent implements** on that branch off fresh `main`, **test-first (TDD —
   ADR 0006):** red → green → refactor per spec Step; plus ticks the spec's Steps +
   drafts its Resolution + adds the `current-feature.md` §History line — **all in
   one PR**. Spec `Status:` = `In review`.
4. **The agent reports** in its own log (`### ROLE · REPORT · DATE`): branch, PR#,
   what changed, how tested (incl. that test-first was followed — exemptions named),
   decisions, uncertainties. Then pauses.
5. **CEO reviews once** — reads the REPORT first, then `gh pr diff`, **tests before
   implementation**: implementation with no driving test is CHANGES REQUESTED by
   default (ADR 0006). Verdict in the log: APPROVED or CHANGES REQUESTED (one
   revision round, then re-review).
6. **On approval** the agent pushes one trivial final commit flipping the spec to
   `Status: DONE (YYYY-MM-DD)`. Owner merges. CEO updates the board history.

## Pre-merge gate (every agent runs it before every REPORT; CI repeats it)

```bash
docker compose exec app ./vendor/bin/pint --test       # PHP style
docker compose exec app ./vendor/bin/phpstan analyse   # static analysis (level 7)
docker compose exec node npm run lint:check            # ESLint
docker compose exec node npm run types:check           # TypeScript
docker compose exec app php artisan test --testsuite=Unit,Feature   # tests (Postgres)
docker compose exec node npm run build                 # assets must build
```

A task is not reportable while any gate step fails. Browser tests
(`guides/testing.md`) run when the task touches user-facing flows.

## Git & process rules (non-negotiable)

- PR → `main`; **never commit to `main`** (hook warns); **never delete branches**;
  milestone tags only.
- One feature → one agent → one PR. Big features → separate numbered sub-features,
  never per-agent slices of one file.
- **Serialization:** all hands-on agents share one working folder — only one
  task's branch is checked out at a time (an agent commits + pushes before
  yielding the folder). The CEO reviews via `gh`/GitHub, never by checking out
  the branch.
- No `Co-Authored-By` trailers (owner preference, kept from the reference project).
- **Docs in sync:** a behavior change updates the affected `context/` doc in the
  same PR. New architectural choices get an ADR (`decisions/`).
- Anything ambiguous, risky, or out-of-spec: the agent **stops and flags it in the
  REPORT** for the CEO to decide (re-route to Senior when architectural) — never
  improvises scope.

## Session & cost discipline

All agents follow `token-optimization.md`: read only the routed doc you need, use
Boost/Graphify instead of spelunking `vendor/`, keep sessions task-scoped, and write
a handoff before ending a long session.
