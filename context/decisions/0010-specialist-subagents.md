# 0010 — Specialists are invokable sub-agents, not standing team sessions

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

After expanding to the 4-agent team (ADR 0008), the owner asked whether the team
should instead be role-specialists: frontend dev, backend dev, database expert,
QA engineer, SEO expert, DevOps engineer, junior dev. The tension: standing
sessions are all the same underlying models — a "frontend developer" session and
a "backend developer" session differ only by prompt, while the domain knowledge
already lives in `context/` docs and skills that every agent reads. What
actually differs between sessions (and drives cost) is the **model tier**, which
is what CEO → Senior/Dev/Runner routes on. Real features also cross specialties
(one page = DB + backend + frontend + SEO), so specialist sessions either fake
their boundary or split one feature across agents — breaking
one-feature-one-agent and multiplying the owner's relay work.

## Decision

Two layers:

1. **Standing team = complexity tiers** (unchanged, ADR 0008): CEO → Senior /
   Dev / Runner. These are separate human-relayed sessions because they run
   different model tiers and hold different responsibilities in the PR lifecycle.
2. **Specialties = sub-agents** in `.claude/agents/`, invokable mid-task by ANY
   team member (or the owner) without a new session or relay:
   - `qa-engineer` — adversarial QA: breaks the change, audits test quality
   - `security-auditor` — auth/authz, injection, mass assignment, secrets
   - `seo-auditor` — public-page head tags, semantics, indexability

   Sub-agents are report-only (they never edit files) so they compose with the
   TDD/review workflow instead of competing with it. Add more the same way when
   a perspective proves repeatedly useful (e.g. `performance-auditor`).

## Consequences

- Specialist expertise costs tokens **only when invoked**, runs in an isolated
  context (findings return, exploration noise doesn't), and needs no owner
  relaying — the three problems standing specialist sessions would have had.
- A Dev-tier session can still get Senior-quality review of a narrow aspect by
  invoking an auditor — routing stays by complexity, expertise arrives on demand.
- The reports are advisory: the invoking agent (and ultimately the CEO review)
  decides what to act on; disagreements surface in the REPORT as usual.
