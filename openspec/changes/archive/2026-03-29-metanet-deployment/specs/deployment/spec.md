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
`_deploy.sh` SHALL run on the server and perform: git pull, composer install (no-dev), artisan migrate, artisan optimize.

#### Scenario: Successful remote deploy
- **WHEN** `_deploy.sh` is executed on the server
- **THEN** it pulls latest code from `origin main`, installs production dependencies, runs pending migrations, and optimises the application cache

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
