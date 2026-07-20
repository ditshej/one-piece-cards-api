## MODIFIED Requirements

### Requirement: Remote deploy script updates the application
`_deploy.sh` SHALL run on the server and perform: git pull, composer install (no-dev), artisan migrate, artisan optimize. Before pulling, it SHALL verify the server is on the `main` branch with a clean working tree, and SHALL abort with a descriptive, non-zero error if either precondition fails. The pull SHALL be fast-forward-only so a diverged `main` fails explicitly rather than creating a merge commit.

#### Scenario: Successful remote deploy
- **WHEN** `_deploy.sh` is executed on the server while checked out on `main` with a clean working tree
- **THEN** it fast-forward pulls latest code from `origin main`, installs production dependencies, runs pending migrations, and optimises the application cache

#### Scenario: Server is on the wrong branch
- **WHEN** `_deploy.sh` is executed while the server is checked out on a branch other than `main`
- **THEN** the script aborts with a non-zero status and an error message naming the current branch and the command to fix it (`git checkout main`), without attempting the pull

#### Scenario: Server working tree has uncommitted changes
- **WHEN** `_deploy.sh` is executed on `main` but the working tree has uncommitted changes
- **THEN** the script aborts with a non-zero status and an error message pointing at `git status`, without attempting the pull

#### Scenario: `main` has diverged from `origin/main`
- **WHEN** `_deploy.sh` is executed on `main` but the local `main` has diverged from `origin/main` (both sides have commits the other lacks)
- **THEN** the fast-forward-only pull fails with a non-zero status rather than creating a merge commit on the server
