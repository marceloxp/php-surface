# Delta for claude-code-install

## ADDED Requirements

### Requirement: Claude Code integration guide

The documentation MUST include a guide for using php-surface with Claude Code agents.

#### Scenario: Discover integration page

- **WHEN** a reader navigates to Integrations → Claude Code
- **THEN** they MUST find step-by-step installation and usage instructions

### Requirement: Project-local installation

The guide MUST document installing php-surface inside a PHP project workspace.

#### Scenario: Composer install in clone

- **WHEN** a reader follows the installation section
- **THEN** they MUST be able to run `./bin/php-surface --version` from the cloned repository after `composer install`

#### Scenario: Invocable path for agents

- **WHEN** Claude Code runs shell commands in the project
- **THEN** the guide MUST explain how to reference the binary via relative path or PATH

### Requirement: CLAUDE.md snippet

The guide MUST provide a copy-paste CLAUDE.md section instructing agents to use php-surface before reading large PHP files.

#### Scenario: Agent workflow rules

- **WHEN** a developer copies the snippet into their project CLAUDE.md
- **THEN** the snippet MUST include the `--stats` → `--filter`/`--search` → `--show` workflow and exit code `3` handling

### Requirement: Troubleshooting

The guide MUST include troubleshooting for common agent integration failures.

#### Scenario: Command not found

- **WHEN** an agent reports `php-surface: command not found`
- **THEN** the troubleshooting section MUST explain path configuration fixes

#### Scenario: Output too large for agent context

- **WHEN** an agent hits exit code `3`
- **THEN** the guide MUST direct users to incremental flags rather than defaulting to `--allow-large-output`

### Requirement: Cross-links

The Claude Code guide MUST link to CLI reference and output examples.

#### Scenario: Deep dive

- **WHEN** a reader needs flag details from the integration guide
- **THEN** internal links MUST route to `cli/reference.md` and `cli/examples.md`
