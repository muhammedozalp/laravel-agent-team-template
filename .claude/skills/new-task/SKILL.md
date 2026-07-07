---
name: new-task
description: CEO-side recipe — turn a request/idea into a numbered feature or fix spec plus a TASK assignment routed to the right agent (Senior/Dev/Runner). Use when the owner asks to plan/spec/assign new work, or when a backlog idea is greenlit.
---

# Create and assign a task (CEO only)

You are the CEO: you spec, route by complexity, and assign; you never implement. Charter:
`context/agent_team/index.md`.

## Steps

1. **Classify:** new capability → `context/feature/`; wrong behavior →
   `context/fix/`; not yet decided → `context/backlog/<slug>.md` and stop here.
2. **Number:** next `NN` in that folder (numbers are stable for life; status lives
   inside the file).
3. **Write the spec** from `context/templates/feature-spec.md` (or `fix-spec.md`):
   a Goal a mid-tier model can't misread, Steps as a tickable checklist, explicit
   out-of-scope. Reference ADRs instead of restating them. If it's big, split into
   separate numbered sub-features — never one spec with parallel workstreams.
4. **Route by complexity** (charter: `context/agent_team/index.md`):
   architectural / risky / judgment-heavy → **Senior**; well-specified checklist
   work → **Dev** (the default); mechanical + bounded → **Runner**. When in
   doubt: Dev — it escalates rather than improvises.
5. **Assign** in the chosen agent's `context/agent_team/log-<role>.md`, next
   global TASK number:
   ```
   ## TASK N — <title> [queued]

   ### N.1 CEO · TASK · YYYY-MM-DD — <summary>
   Spec: context/feature/NN-<slug>.md · Branch: iNNN/<slug>
   <constraints, what done looks like, anything the spec delegates to judgment>
   ```
6. **Board:** add the row (with the Agent column) to
   `context/agent_team/board.md` Active table and update its STATUS paragraph
   (what the owner should relay to which agent next).

## Review reminders (for later, same task)

Read the agent's REPORT before the diff · `gh pr diff` once · verdict as
`### N.M CEO · REVIEW` (APPROVED / CHANGES REQUESTED) · after merge: move the
board row to Task history.
