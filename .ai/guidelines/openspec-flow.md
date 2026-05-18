## Git + OpenSpec Feature Branch Flow

**Rule:** Every new feature or significant change ALWAYS starts with `/opsx:propose` — never implement directly, not even in Plan Mode. Only after the propose commit may `/opsx:apply` begin.

Every OpenSpec change gets its own feature branch and lands on `main` as a single merge commit (`--no-ff`). No squash, no rebase-merge. No direct push to `main` — always via PR with CI passing.

### Branch Naming Convention

```
feat/<change-name>      # e.g. feat/card-management
```

> **Autonomous mode (AGENT_MISSION):** When working through a full roadmap autonomously, per-change CHECKPOINTs are skipped. Instead, after the session is complete, the agent must produce a mandatory stop — presenting a full summary of all changes and optionally opening a GitHub PR for review.

### Workflow per Change

```bash
# 0. Explore (optional)
# /opsx:explore — investigate ideas and requirements before proposing
# → CHECKPOINT: Present findings to user → wait for OK before proposing

# 1. Create branch
git checkout -b feat/<change-name>

# 2. Propose
openspec new change "<change-name>"
# /opsx:propose — create proposal.md, specs/, design.md, tasks.md
# → Commit: "docs(<change-name>): add proposal, design and tasks"
# → CHECKPOINT: Present proposal summary → wait for OK before implementing

# 3. Implementation (TDD)
# /opsx:apply — work through tasks
# → Commit(s): "feat(<change-name>): ...", "test(<change-name>): ...", etc.

# 4. Verify
# /opsx:verify — checks Completeness, Correctness, Coherence against specs
# → Fix all CRITICALs before proceeding

# 5. AI Review
# laravel-simplifier Agent — automated review (spawn parallel subagents)
# → Fix critical findings, commit: "refactor(<change-name>): apply review feedback"
# → CHECKPOINT: Present change summary:
#     - What changed (architecture, new/modified files)
#     - Test results (N passed)
#     - How to review manually (git diff, which pages/endpoints to test)
#   → Wait for user OK before archiving

# 6. Archiving
# /opsx:archive — close change, merge specs
# → Commit: "docs(<change-name>): archive change"

# 7. Clean up fixup commits and push
git fetch origin && git rebase -i --autosquash origin/main   # collapses `fixup!` commits; no-op otherwise
git push -u origin feat/<change-name>
gh pr create --title "feat(<change-name>): <description>"
# → CI must pass (tests + lint), then merge via GitHub ("Create a merge commit")

# 8. Merge and cleanup
gh pr merge --merge --delete-branch
git checkout main && git pull && git remote prune origin
```

### Resulting History on main

```
*   Merge pull request #43 from feat/list-packs
|\
| * docs(list-packs): archive change
| * refactor(list-packs): apply review feedback
| * feat(list-packs): add packs() and pack() endpoints
| * docs(list-packs): add proposal, design and tasks
|/
*   Merge pull request #42 from feat/prev-change
```

Use `git log --first-parent main` to see only the merge commits (one per change).

Each feature follows: Planning → Implementation → Verify → Review → Archiving.
Use the change name as commit scope for every commit on that branch.
Multiple commits per phase are fine — commit as often as makes sense (feat, fix, test, refactor, etc.).
