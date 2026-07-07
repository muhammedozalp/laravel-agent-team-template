# Agent team charter — CEO → Senior / Dev / Runner

_Git-tracked charter; **only the CEO edits it**. Process rules all agents follow
live in `../ai-interaction.md`; rationale in `../decisions/0008-four-agent-team.md`.
The board and logs are **local (git-ignored)** — durable record = git (branches,
PRs, spec Resolutions, ADRs, `current-feature.md` history)._

## The team

| Role | Model tier | Launched from | Does | Role file | Mailbox |
|---|---|---|---|---|---|
| **CEO** | strong (Opus-class) | parent workspace | orchestrates: writes specs & tasks, assigns by complexity, reviews every PR read-only. **No hands-on edits.** | — (this charter + `ai-interaction.md`) | writes into each agent's log + `board.md` |
| **Senior** | strong (Opus-class) | repo root (hooks active) | hard / risky / architectural / judgment-heavy tasks | `@role-senior.md` | `log-senior.md` |
| **Dev** | mid (Sonnet-class) | repo root (hooks active) | well-specified features with a clear checklist — **the default assignee** | `@role-dev.md` | `log-dev.md` |
| **Runner** | small (Haiku-class) | repo root (hooks active) | fast, mechanical, bounded jobs | `@role-runner.md` | `log-runner.md` |

The human **owner** relays messages between sessions, sets priorities, and is the
only one who merges.

**Specialist perspectives are sub-agents, not extra team members** (ADR 0010):
any agent can invoke `qa-engineer`, `security-auditor`, or `seo-auditor`
(`.claude/agents/`) mid-task — report-only, isolated context, no owner relay.
Invoke the relevant auditor before REPORTing anything touching their domain.

## Rules

- **Routing:** one feature → one agent → one PR. Parallelism = different features
  on different agents **only when they touch different files**. Big features →
  separate numbered sub-features, one agent each — never per-agent slices of one
  file. When in doubt about complexity, assign to Dev; Dev escalates rather than
  improvises.
- **Star topology:** only the CEO assigns tasks. Agents propose follow-ups by
  flagging them in their REPORT; the CEO disposes (decides + assigns, or sends to
  `../backlog/`). Agents never assign to each other and never edit another
  agent's log or role file.
- **Numbering:** task numbers (`TASK N`) are global, assigned by the CEO. Within a
  task, entries are `### N.M ROLE · TYPE · DATE — summary`
  (TYPE = TASK / REPORT / REVIEW / NOTE / REPLY). The CEO owns the `N.M`
  numbering — agents head their REPORT `ROLE · REPORT · DATE` and the CEO numbers
  it at review.
- **Serialization:** all hands-on agents share ONE working folder — one agent's
  branch checked out at a time. An agent commits + pushes before yielding the
  folder. The CEO assigns the exact `iNNN/<slug>` branch name up front.
- **Files:** tracked = this charter + the three `role-*.md` + `templates/`.
  Local (git-ignored) = `board.md`, `log-*.md`, `handoff.md` — volatile
  scratchpads that would flood PRs. Fresh project: copy them from `templates/`.
- **Scaling down:** a small project may simply not launch Senior/Runner — the
  charter degrades gracefully to CEO+Dev without edits.
