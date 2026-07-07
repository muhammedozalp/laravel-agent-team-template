# Token optimization — session, context & cost policy

_Canonical cost policy for every Claude session on this project. AI development cost
is a first-class constraint here; these rules are as binding as the git rules.
Rationale and alternatives: `decisions/0005-token-optimization-stack.md`._

## The stack (three layers, adopt in this order)

### 1. Native context discipline (free — the biggest lever)

- **Thin-router `CLAUDE.md`** (< ~80 lines): points into `context/`; agents read
  ONLY the routed doc they need for the task at hand, never the whole tree.
- **Every fact has one home** — no doc restates another, so no fact is paid for
  twice per session.
- **Task-scoped sessions:** one task = one session where practical. Start from the
  spec (`feature/NN-slug.md`), not from re-explaining the project.
- **Subagents for exploration:** broad searches, long file surveys, and research go
  to subagents — grep noise stays out of the main window; only conclusions return.
- **Session handoff:** before ending a long or unfinished session, write
  `agent_team/handoff.md` (git-ignored): goal, branch, changed files, decisions,
  failing tests, exact next step. Resume with "Read agent_team/handoff.md and
  continue" instead of replaying history. A Stop-hook reminder is acceptable; the
  habit is mandatory.
- **Watch the window:** `/context` when a session feels heavy; `/compact` at ~70%
  capacity (docs re-inject from disk afterwards — that is why facts live in files).

### 2. Laravel Boost MCP (installed `--dev`)

Ask the app instead of reading it: routes, models, **live DB schema**, logs, tinker,
artisan, and **version-pinned Laravel-ecosystem doc search**. This kills the two most
expensive behaviors in a Laravel repo: `vendor/` spelunking and wrong-version API
hallucination (rework is the hidden token cost).

- Setup: `composer require laravel/boost --dev` is already in the template; run
  `docker compose exec app php artisan boost:install` once per project.
- Rule: **schema/route/config questions go to Boost tools first**, file reads second.

### 3. Graphify (codebase + PostgreSQL schema as a knowledge graph)

Whole-codebase questions ("what calls this?", "where is X decided?") cost ~1.7k
tokens against the graph vs ~100k+ reading files. Code parsing is local (no API
cost); install with the postgres extra so the DB schema lands in the same graph:

```bash
uv tool install "graphifyy[postgres]"
graphify install          # registers the /graphify skill
# then in a session:  /graphify .
```

- Rule: **rebuild the graph on merge to `main`** (CI job or post-merge hook) —
  a stale graph answers confidently and wrongly. Never trust graph answers about
  files changed on the current branch; read those directly.

## Optional additions (per project, not default)

- **Context7 MCP** — version-specific docs for the JS/frontend half (React,
  Inertia, shadcn/ui, TypeScript — which Boost's Laravel-ecosystem docs don't
  cover). Since the React-kit pivot (ADR 0007) this is **recommended once real
  frontend work starts**; free tier is 1k requests/month.
- **Graphiti / Mem0 memory graphs** — real cross-session memory, but the graph-DB +
  extraction-LLM overhead is disproportionate for a small team; `handoff.md` +
  ADRs + Graphify cover ~90% of the need at zero cost.

## Cost rules of thumb

- Model routing is a cost tool (the point of the 4-agent split, ADR 0008): CEO and
  Senior on the strong model, Dev on the mid model, Runner on the small model for
  mechanical chores (renames, checklist ticking, running commands).
- Prefer skills (`.claude/skills/`) for repeatable recipes — loaded only when
  triggered, instead of living permanently in `CLAUDE.md`.
- Don't paste build output, full logs, or whole files into chat; reference paths and
  let the agent read the slice it needs (`artisan` logs go through Boost).
- Keep `CLAUDE.md` thin forever: anything that grows there moves to a `context/`
  doc and becomes a router line.
