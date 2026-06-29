# php-surface

Structural explorer for PHP source files. Outputs a compact map of classes, traits and interfaces for incremental exploration.

## Requirements

- PHP 8.3+

## Install

```bash
composer install
```

## Usage

```bash
./bin/php-surface path/to/File.php
./bin/php-surface path/to/File.php --help
./bin/php-surface path/to/File.php --text
./bin/php-surface path/to/File.php --filter save
./bin/php-surface path/to/File.php --visibility public
./bin/php-surface path/to/File.php --full
./bin/php-surface path/to/File.php --show ClassName::method
./bin/php-surface path/to/File.php --stats
```

## Runtime PHP

By default, `bin/php-surface` uses the first compatible PHP 8.3+ binary in your `PATH`.

Override with:

```bash
PHP_SURFACE_BIN_PATH=/usr/bin/php8.3 ./bin/php-surface File.php
```

## Output size guard

Default map output is capped at **8 KB** to keep AI context lean. When the rendered output exceeds the limit, the tool exits with code `3` and prints guidance on stderr (JSON or text, matching `--text`).

Override the limit:

```bash
PHP_SURFACE_MAX_OUTPUT_BYTES=32000 ./bin/php-surface Large.php
```

Or bypass the guard explicitly:

```bash
./bin/php-surface Large.php --allow-large-output
```

## Exit codes

| Code | Meaning |
|------|---------|
| `0` | Success |
| `1` | Usage error (missing argument, invalid flag, symbol not found) |
| `2` | File error (missing file, invalid extension, parse error) |
| `3` | Output too large (use `--stats`, filters, `--show`, or `--allow-large-output`) |

## Tests

```bash
composer test
```

Or directly:

```bash
./vendor/bin/pest
```

CLI output is covered by snapshot tests in `tests/Feature/CliSnapshotTest.php`. Golden files live under `tests/.pest/snapshots/`. After intentional output changes, refresh them with:

```bash
./vendor/bin/pest --update-snapshots
```
