### Requirement: CONTRIBUTING.md exists at repository root
The repository SHALL contain a `CONTRIBUTING.md` file that guides contributors through the development workflow.

#### Scenario: File is present and covers the OpenSpec workflow
- **WHEN** a contributor opens `CONTRIBUTING.md`
- **THEN** it SHALL explain that every change starts with `/opsx:propose` before any implementation

#### Scenario: File covers the feature branch convention
- **WHEN** a contributor reads the contribution guide
- **THEN** it SHALL specify the branch naming convention (`feat/<change-name>`) and that squash merges are not used

#### Scenario: File covers TDD requirement
- **WHEN** a contributor reads the contribution guide
- **THEN** it SHALL state that tests must be written before implementation and how to run the test suite

#### Scenario: File covers code style
- **WHEN** a contributor reads the contribution guide
- **THEN** it SHALL mention that Pint is used for code formatting and how to run it
