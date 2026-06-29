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
./bin/php-surface path/to/File.php --text
./bin/php-surface path/to/File.php --filter save
./bin/php-surface path/to/File.php --visibility public
./bin/php-surface path/to/File.php --full
./bin/php-surface path/to/File.php --show ClassName::method
```

## Runtime PHP

By default, `bin/php-surface` uses the first compatible PHP 8.3+ binary in your `PATH`.

Override with:

```bash
PHP_SURFACE_BIN_PATH=/usr/bin/php8.3 ./bin/php-surface File.php
```

## Exit codes

| Code | Meaning |
|------|---------|
| `0` | Success |
| `1` | Usage error (missing argument, invalid flag, symbol not found) |
| `2` | File error (missing file, invalid extension, parse error) |

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
