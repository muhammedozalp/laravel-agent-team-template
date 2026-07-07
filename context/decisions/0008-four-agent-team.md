# 0008 — Four-agent team: CEO + Senior/Dev/Runner (supersedes 0003)

- **Date:** 2026-07-07
- **Status:** Accepted (supersedes [0003](0003-two-agent-team.md))

## Context

The template launched with a 2-agent Lead/Dev team (ADR 0003) — a deliberate
simplification of the reference project's proven 4-agent system. The owner now
wants the full model from the start: the reference project's routing-by-complexity
(hard/architectural work on a strong model, well-specified features on a mid
model, mechanical chores on a small model) is where the token savings actually
come from, and retrofitting roles mid-project is churn.

## Decision

Four agents, mechanics identical to the reference project:

| Role | Model tier | Does |
|---|---|---|
| **CEO** | strong (Opus-class) | orchestrates: writes specs & TASK entries, assigns by complexity, reviews every PR read-only. **No hands-on edits.** |
| **Senior** | strong (Opus-class) | hard / risky / architectural / judgment-heavy tasks |
| **Dev** | mid (Sonnet-class) | well-specified features with a clear checklist (the default assignee) |
| **Runner** | small (Haiku-class) | fast, mechanical, bounded jobs (renames, find/replace, running builds, ticking checklists) |

Kept from the reference project: files-as-mailbox (`board.md` + one `log-<role>.md`
per hands-on agent, git-ignored; charter + role files tracked), star topology (only
the CEO assigns; agents propose in REPORTs), global task numbers with
`### N.M ROLE · TYPE · DATE` entries, human owner as relay and merger,
**serialization** (one shared checkout — one agent's branch at a time, the CEO
review already serializes the critical path, so worktrees add risk without speed).

Improvements over the reference version:

- Model tiers are named generically (Opus-class/Sonnet-class/Haiku-class) so the
  charter survives model generations.
- Every role file carries the same 5-step lifecycle verbatim — only the `## Your
  scope` section differs — so role drift can't creep in.
- A per-session `handoff.md` convention (from the 2-agent era) is kept for all
  agents.

## Consequences

- Cheaper tokens per unit of work: mechanical chores stop burning mid-tier
  sessions; architectural work stops being under-modeled.
- More relay work for the human owner (four sessions instead of two at full
  utilization) — mitigated by the board's STATUS paragraph always naming the next
  relay action.
- ADR 0003's escape hatch inverted: a project that feels heavy can simply not
  launch Senior/Runner sessions — the charter degrades gracefully to Lead+Dev.
