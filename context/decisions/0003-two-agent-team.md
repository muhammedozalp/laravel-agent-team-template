# 0003 — Two-agent team: Lead + Dev, files-as-mailbox coordination

- **Date:** 2026-07-07
- **Status:** Superseded by [0008](0008-four-agent-team.md) (owner chose the full
  4-agent model from the reference project)

## Context

The reference project evolved a 4-agent team (CEO + Senior/Dev/Runner, its
ADRs 0005→0007): separate human-launched Claude Code sessions coordinating through
git-ignored markdown logs, star topology, one PR per task, serialized shared folder.
That scale fits a busy production site; a fresh project doesn't need four sessions,
and every extra agent adds relay overhead for the human owner and duplicated context
cost.

## Decision

Collapse to **two agents** (this is essentially the reference project's own earlier lead/worker model,
kept deliberately):

- **Lead** — launched from the parent workspace; strong model; writes specs and
  TASK entries, reviews every PR read-only (`gh pr diff`); never edits files.
- **Dev** — launched from the repo root (hooks active); mid model; implements every
  task on `iNNN/<slug>` → one PR; drops to a small model for mechanical chores
  instead of a third agent.

Mechanics kept from the reference project: files-as-mailbox (`agent_team/board.md` +
`log-dev.md`, git-ignored; charter + role file tracked), global task numbers,
`### N.M ROLE · TYPE · DATE` entries, human owner as relay and merger, no worktrees
— the folder serializes naturally since only Dev edits.

## Consequences

- Half the sessions to relay between; the review gate (the real quality mechanism)
  is unchanged.
- Difficulty routing happens by **model switch within the Dev session**, not by
  agent count. If a project later needs a second implementer, add `role-senior.md`
  + `log-senior.md` and copy the reference routing table — the charter format already
  supports it.
