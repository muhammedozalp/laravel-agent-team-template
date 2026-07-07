# 0005 — Token-optimization stack: native discipline + Laravel Boost + Graphify

- **Date:** 2026-07-07
- **Status:** Accepted

## Context

AI development cost is a first-class constraint for the owner. Research (2026-07)
compared five approaches: (1) native Claude Code context discipline, (2) Laravel
Boost MCP, (3) Graphify (codebase/DB knowledge-graph skill, ~78k★, the tool the
owner referred to), (4) Context7 doc-retrieval MCP, (5) Graphiti/Mem0 memory graphs.

## Decision

Adopt three layers by default (policy doc: `../token-optimization.md`):

1. **Native discipline** — thin-router CLAUDE.md, one-home-per-fact docs, subagent
   isolation, session handoff file, task-scoped sessions. Highest ROI, zero cost.
2. **Laravel Boost MCP** (`--dev`) — app introspection + version-pinned docs search;
   eliminates vendor-spelunking and wrong-version rework.
3. **Graphify** with the `[postgres]` extra — whole-codebase + DB-schema questions at
   ~1.7k tokens/query instead of 100k+; local parsing, rebuilt on merge to `main`.

**Deferred, not rejected:** Context7 (overlaps Boost's docs search until heavy
frontend work appears; free tier now 1k req/mo) and Graphiti/Mem0 (graph-DB +
extraction-LLM ops cost disproportionate for two agents; handoff + ADRs + Graphify
cover the need).

## Consequences

- Two installs per new project (`boost:install`, `graphify install`) — scripted in
  `../guides/new-project-from-template.md`.
- Graphify is pre-1.0: pin the version; a stale graph is the known failure mode
  (mitigated by the rebuild-on-merge rule).
- Boost note: some Claude Code versions had project-scope `.mcp.json` issues
  (laravel/boost#408) — if tools don't appear, register the server at user scope.
