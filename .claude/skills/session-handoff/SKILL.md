---
name: session-handoff
description: Write or resume a session handoff so the next session starts warm instead of replaying history. Use when ending a session with unfinished work, when the context window is getting heavy, or when asked to "continue where we left off".
---

# Session handoff

Token policy: `context/token-optimization.md`. The handoff file is
`context/agent_team/handoff.md` (git-ignored).

## Writing one (end of an unfinished session)

Fill `context/agent_team/templates/handoff.template.md` → save as
`context/agent_team/handoff.md`. Rules:

- **Exact next step**, not a vague direction ("run the Feature suite; UserTest
  fails on the unique-email assertion — fix the factory state", not "continue
  testing").
- Decisions include the *why* in one clause — the next session must not re-litigate.
- Failing state verbatim but short: the assertion line, not the whole trace.
- Commit + push the branch first; the handoff records repo state, it doesn't
  replace git.

## Resuming from one

1. Read `context/agent_team/handoff.md`, the spec it names, and nothing else yet.
2. Verify reality still matches (branch checked out? tests still failing the same
   way?) — trust git over the file when they disagree.
3. Do the "exact next step". Delete or rewrite the handoff once absorbed — a stale
   handoff is worse than none.
