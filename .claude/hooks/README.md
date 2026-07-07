# Guardrail hooks

PreToolUse hooks wired in `../settings.json`. All read the hook JSON payload on
stdin (need `jq`), resolve the repo root from `$CLAUDE_PROJECT_DIR` with
`cwd`/`$PWD` fallback, and exit 0 on pass-through.

| Hook | Matcher | Effect |
|---|---|---|
| `block-generated-and-env-edits.sh` | `Write\|Edit` | **DENIES** edits to `vendor/`, `node_modules/`, `public/build/`, `storage/`, `.env*` (allows `.env.example`/`sample`/`template` and `.env.*.example` per-env templates) |
| `warn-commit-on-main.sh` | `Bash` (`git commit*`) | **WARNS** (never blocks) on commits while on `main`/`master` |
| `warn-edit-merged-migration.sh` | `Write\|Edit` | **WARNS** (never blocks) when editing a migration that already exists on `main` — append-only rule |
| `check-ai-tooling.sh` | SessionStart | **WARNS** when per-machine/per-project AI tooling is missing (Graphify binary/graph, Boost skills) |

## Activation

Hooks are not auto-trusted: on first launch from the repo root, approve them via
`/hooks`. Headless `claude -p` does **not** load project-directory hooks. The CEO
session (launched from the parent workspace) intentionally runs without them — it
makes no edits.

## Verify a hook by hand

```bash
echo '{"tool_input":{"file_path":"'$PWD'/.env"},"cwd":"'$PWD'"}' \
  | bash .claude/hooks/block-generated-and-env-edits.sh
# expect: permissionDecision "deny"

echo '{"cwd":"'$PWD'"}' | bash .claude/hooks/warn-commit-on-main.sh
# expect: systemMessage warning iff current branch is main
