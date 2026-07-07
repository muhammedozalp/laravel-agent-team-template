#!/usr/bin/env bash
# PreToolUse guardrail (Bash: git commit*): WARN (never block) when committing
# directly on main/master. Project rule: work on iNNN/<slug> branches -> PR.
set -euo pipefail

payload="$(cat)"

# Only act on `git commit …` commands — the matcher in settings.json is a broad
# "Bash", so the command filter lives here.
command="$(jq -r '.tool_input.command // empty' <<<"$payload")"
case "$command" in
  "git commit"*|*"&& git commit"*|*"; git commit"*) ;;
  *) exit 0 ;;
esac

root="${CLAUDE_PROJECT_DIR:-$(jq -r '.cwd // empty' <<<"$payload")}"
root="${root:-$PWD}"

# symbolic-ref (not rev-parse) so the unborn branch right after `git init` counts too
branch="$(git -C "$root" symbolic-ref --short HEAD 2>/dev/null || echo '')"

if [ "$branch" = "main" ] || [ "$branch" = "master" ]; then
  jq -n '{
    systemMessage: "WARNING: you are about to commit directly on \"main\". Project rule: never commit to main — work on an iNNN/<slug> branch and open a PR (context/ai-interaction.md). (Warning only; the commit is NOT blocked.)"
  }'
fi

exit 0
