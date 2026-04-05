## Requirements

### Requirement: Consuming apps are represented as password-less User accounts
The system SHALL store consuming app identities as User model records with a name and email address. Users SHALL NOT require a password.

#### Scenario: User record exists for each consuming app
- **WHEN** `token:create` is run for a new app
- **THEN** a User record exists in the `users` table with the given name and email

### Requirement: Artisan command creates a user and issues a Sanctum token
The system SHALL provide `php artisan token:create {name} {email}` which creates a password-less User, issues a Sanctum Personal Access Token, and displays the plaintext token once in the console.

#### Scenario: Token creation outputs plaintext token
- **WHEN** `php artisan token:create "Brook Deck Sim" "brook@apps.ditshej.ch"` is executed
- **THEN** a User record is created with the given name and email
- **AND** a Sanctum Personal Access Token is issued for that user
- **AND** the plaintext token is displayed once in the console output
- **AND** only the token hash is stored in `personal_access_tokens`

#### Scenario: Name and email are required
- **WHEN** `php artisan token:create` is run without arguments
- **THEN** the command outputs an error and creates no user or token

### Requirement: last_used_at is updated on valid requests
The system SHALL update `last_used_at` on the `personal_access_tokens` record upon each successful authentication.

#### Scenario: last_used_at reflects latest request
- **WHEN** a valid Bearer token is used to authenticate a request
- **THEN** the corresponding `personal_access_tokens` record has `last_used_at` set to the current timestamp
