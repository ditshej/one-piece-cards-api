## ADDED Requirements

### Requirement: Local deploy script sources credentials from gitignored file
`deploy.sh` SHALL read `DEPLOY_SSH_CONNECTION` and `DEPLOY_PATH` from `.env.deploy` (gitignored). It SHALL fail gracefully if `.env.deploy` is missing.

#### Scenario: Successful deploy invocation
- **WHEN** `.env.deploy` exists with valid `DEPLOY_SSH_CONNECTION` and `DEPLOY_PATH`
- **THEN** `bash deploy.sh` SSHes into the server and runs `_deploy.sh` in `DEPLOY_PATH`

#### Scenario: Missing .env.deploy
- **WHEN** `.env.deploy` does not exist
- **THEN** the script exits with a non-zero status and a descriptive error message

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

### Requirement: Credentials template is committed to the repository
`.env.deploy.example` SHALL be committed and contain all required variable names with placeholder values.

#### Scenario: Developer onboarding
- **WHEN** a developer clones the repository
- **THEN** they can copy `.env.deploy.example` to `.env.deploy` and fill in their own credentials

### Requirement: `.env.deploy` is excluded from version control
`.gitignore` SHALL contain an entry for `.env.deploy` to prevent accidental credential commits.

#### Scenario: Git status with credentials file present
- **WHEN** `.env.deploy` exists locally
- **THEN** `git status` does not show it as a tracked or untracked file
