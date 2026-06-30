# Delta for cli-reference

## ADDED Requirements

### Requirement: Complete CLI flag reference

The documentation MUST list every option exposed by `php-surface --help`.

#### Scenario: Help parity

- **WHEN** a reader opens the CLI reference page
- **THEN** every flag in `Application.php::printHelp()` MUST be documented with description and example usage

Flags include: `--text`, `--filter`, `--search`, `--visibility`, `--show`, `--stats`, `--full`, `--allow-large-output`, `-h`/`--help`, `-V`/`--version`.

### Requirement: Environment variables

The documentation MUST describe all environment variables read by the CLI.

#### Scenario: Output limit override

- **WHEN** a reader looks up output size configuration
- **THEN** the docs MUST document `PHP_SURFACE_MAX_OUTPUT_BYTES` with default `8192`

#### Scenario: PHP binary override

- **WHEN** a reader needs a specific PHP 8.3+ binary
- **THEN** the docs MUST document `PHP_SURFACE_BIN_PATH`

### Requirement: Exit codes

The documentation MUST describe exit codes 0 through 3 with triggering conditions.

#### Scenario: Usage error

- **WHEN** the CLI exits with code `1`
- **THEN** the docs MUST state it indicates usage errors such as missing file argument or unknown symbol for `--show`

#### Scenario: File error

- **WHEN** the CLI exits with code `2`
- **THEN** the docs MUST state it indicates missing/unreadable files or parse failures

#### Scenario: Output too large

- **WHEN** the CLI exits with code `3`
- **THEN** the docs MUST state output exceeded the guard limit and stderr contains remediation hints

### Requirement: Incremental exploration workflow

The documentation MUST describe the recommended exploration sequence for large PHP files.

#### Scenario: Stats first

- **WHEN** a reader explores a large file
- **THEN** the docs MUST recommend starting with `--stats` before requesting the full symbol map

#### Scenario: Show on demand

- **WHEN** a reader needs method implementation
- **THEN** the docs MUST recommend `--show ClassName::method` after narrowing with `--filter` or `--search`

### Requirement: Output examples section

The documentation MUST provide a dedicated page with command invocations and stdout examples.

#### Scenario: JSON and text tabs

- **WHEN** a reader views an output example
- **THEN** the page MUST show both default JSON and `--text` variants where applicable using Material content tabs

#### Scenario: Snapshot alignment

- **WHEN** CLI output format changes in a release
- **THEN** documented examples MUST be updated to match `tests/.pest/snapshots/Feature/CliSnapshotTest/`
