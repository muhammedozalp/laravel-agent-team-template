# 0006 — TDD is the default development approach

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

AI agents write plausible-looking code fast; the failure mode is code that looks
right and quietly isn't. With a CEO who reviews diffs but never runs the app
hands-on, the review needs an artifact that states intended behavior independently
of the implementation. Test-first development provides exactly that, and is the
widely-advised practice for AI-agent teams: the test is the spec made executable,
written before the code that satisfies it.

## Decision

**Red → green → refactor is the default for all production code** (details:
`../guides/testing.md` § TDD):

1. **Red** — from the spec's Steps, write the smallest failing test naming the next
   behavior. Run it; confirm it fails **for the expected reason**.
2. **Green** — write the minimum code to pass. Run the test.
3. **Refactor** — clean up with the suite green; then next behavior.

Process hooks: Dev's REPORT states test-first was followed (and where it wasn't,
why); the CEO's review reads tests before implementation and treats
implementation-without-a-driving-test as CHANGES REQUESTED by default. Fixes were
already regression-test-first; this generalizes the rule.

Pragmatic exemptions (declared in the REPORT, not silent): configuration/wiring,
Blade markup/styling with no behavior, generated scaffolding, and exploratory
spikes — a spike's code is either discarded or re-driven by tests before the PR.

## Consequences

- Every PR's diff starts with tests that read as the feature's specification —
  review quality rises, and the CEO can reject cheaply.
- Slightly more upfront work per feature; recovered in fewer review rounds and
  fewer plausible-but-wrong implementations.
- The pyramid stays honest: TDD naturally produces feature/unit tests; browser
  tests remain a thin post-hoc layer for critical flows.
