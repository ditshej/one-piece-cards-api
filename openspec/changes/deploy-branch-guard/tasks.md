## 1. Harden `_deploy.sh`

- [ ] 1.1 Add a branch guard before the pull: read the current branch with `git rev-parse --abbrev-ref HEAD` and, if it is not `main`, print a descriptive error (naming the actual branch and the `git checkout main` fix) and `exit 1`
- [ ] 1.2 Add a clean-tree guard: if `git diff-index --quiet HEAD --` reports changes, print a descriptive error (pointing at `git status`) and `exit 1`
- [ ] 1.3 Change the pull to `git pull --ff-only origin main`
- [ ] 1.4 Verify `set -e` is preserved and the composer/migrate/optimize steps are unchanged

## 2. Manual Test Plan (documented in change)

- [ ] 2.1 Simulate wrong branch locally: on a throwaway clone, `git checkout -b some-branch`, run the guard portion, confirm it aborts with the clear message and non-zero exit
- [ ] 2.2 Simulate dirty tree: on `main` with an uncommitted change, confirm the clean-tree guard aborts with the clear message and non-zero exit
- [ ] 2.3 Happy path: on clean `main`, confirm `git pull --ff-only origin main` runs and the deploy proceeds

## 3. Real Deploy Verification

- [ ] 3.1 Run `bash deploy.sh` against the server (now on `main`) and verify it completes without errors
- [ ] 3.2 Verify `https://op-cards.ditshej.ch/up` returns HTTP 200
