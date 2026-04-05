## ADDED Requirements

### Requirement: Local script creates a token on the remote server via SSH
The system SHALL provide a `create-token.sh` script that reads SSH credentials from `.env.deploy`, connects to the server via SSH, and runs `php artisan token:create {name} {email}` remotely, printing the plaintext token in the local console.

#### Scenario: Successful remote token creation
- **WHEN** `.env.deploy` exists with valid `DEPLOY_USER`, `DEPLOY_HOST`, `DEPLOY_PORT`, and `DEPLOY_PATH`
- **AND** `./create-token.sh "App Name" "app@example.com"` is executed
- **THEN** the script SSHes into the server and runs `php artisan token:create "App Name" "app@example.com"` in `DEPLOY_PATH`
- **AND** the plaintext token is printed in the local terminal

#### Scenario: Missing .env.deploy
- **WHEN** `.env.deploy` does not exist
- **THEN** the script exits with a non-zero status and a descriptive error message
- **AND** no SSH connection is attempted

#### Scenario: Missing arguments
- **WHEN** `./create-token.sh` is called without name and email arguments
- **THEN** the script exits with a non-zero status and a usage message

### Requirement: API documentation explains token acquisition for consumers
The Scramble API description (`config/scramble.php` `info.description`) SHALL explicitly state that the API requires a Bearer token and that consumers must contact the owner to obtain one.

#### Scenario: Consumer reads the API docs
- **WHEN** a consumer visits `/docs/api`
- **THEN** the description states that authentication requires a Bearer token
- **AND** the description instructs them to contact the owner to request access

### Requirement: README documents API usage and operational commands
`README.md` SHALL be a project-specific document covering: project purpose, base URL, authentication (Bearer token + contact for access), endpoint overview, deployment (`./deploy.sh`), and token creation (`./create-token.sh`).

#### Scenario: New consumer reads the README
- **WHEN** a developer visits the repository root
- **THEN** they can understand what the API does, how to authenticate, and which endpoints exist
- **AND** they know how to request access (contact the owner)

#### Scenario: Operator deploys and issues a token
- **WHEN** the operator reads the README
- **THEN** they can run `./deploy.sh` to deploy and `./create-token.sh` to issue a token without additional documentation
