# Commands

Reference for the `php-surface` CLI. For copy-paste output samples, see [Output Examples](examples.md).

## Synopsis

```bash
php-surface <file.php> [options]
```

The `<file.php>` argument must be the path to a PHP source file — **use an absolute path** when possible (see [Installation](../getting-started/installation.md)).

## Arguments

| Argument | Required | Description |
|----------|----------|-------------|
| `file.php` | Yes | PHP source file to analyze. Must have a `.php` extension and be readable. |

## Options

### Output format

#### `--text`

Print human-readable output instead of JSON.

```bash
php-surface /absolute/path/to/File.php --text
```

Applies to all output modes (default map, `--stats`, `--search`, `--show`).

#### `--full`

Include structured parameters and full docblocks in the symbol map.

```bash
php-surface /absolute/path/to/File.php --full
```

Only affects the default map mode (not `--stats`, `--search`, or `--show`). Combine with `--filter` or `--visibility` to keep output small.

### Exploration modes

These modes change **what** is printed. Only one primary mode runs per invocation; the first matching branch in the CLI wins: `--show` → `--search` → `--stats` → default map.

#### `--stats`

Print a compact statistical summary instead of the full symbol map.

```bash
php-surface /absolute/path/to/Large.php --stats --text
```

Useful as the first step on large files. Respects `--filter` and `--visibility` applied before collection.

#### `--search <term>`

Find symbols, methods, and source lines matching `<term>` (case-insensitive).

```bash
php-surface /absolute/path/to/File.php --search nested
php-surface /absolute/path/to/File.php --search charge --text
```

When `--visibility` is set, search runs on the visibility-filtered symbol set.

#### `--show <symbol>`

Extract the source body of a method. `<symbol>` can be:

- a method name alone (`save`) — matches across symbols
- a qualified name (`ClassName::save` or `Namespace\ClassName::save`)

```bash
php-surface /absolute/path/to/File.php --show save
php-surface /absolute/path/to/File.php --show OrderService::findActive
```

Exits with code `1` if no symbol matches.

### Filters

Filters narrow the symbol set. They apply to the default map and `--stats`. With `--search`, only `--visibility` applies (before search).

#### `--filter <name>`

Keep only methods whose **name contains** `<name>`. Matching is **case-sensitive**.

```bash
php-surface /absolute/path/to/File.php --filter save
php-surface /absolute/path/to/File.php --filter find
```

Symbols with no matching methods are omitted from the output.

#### `--visibility <level>`

Keep only methods with the given visibility.

| Value | Description |
|-------|-------------|
| `public` | Public methods only |
| `protected` | Protected methods only |
| `private` | Private methods only |

```bash
php-surface /absolute/path/to/File.php --visibility public
php-surface /absolute/path/to/File.php --visibility protected --filter find
```

### Output size

#### `--allow-large-output`

Skip the output size guard (default limit: 8 KB). Use only when you intentionally need the full map or a large `--show` result.

```bash
php-surface /absolute/path/to/Large.php --allow-large-output
```

Prefer `--stats`, `--filter`, `--search`, or `--show` before bypassing the guard.

### Information

#### `-h`, `--help`

Print usage help and exit with code `0`.

```bash
php-surface --help
```

#### `-V`, `--version`

Print version and exit with code `0`.

```bash
php-surface --version
```

## Environment variables

| Variable | Default | Description |
|----------|---------|-------------|
| `PHP_SURFACE_BIN_PATH` | Auto-detected | PHP 8.3+ binary used by the `bin/php-surface` wrapper. Set when `php` on your `PATH` is too old. |
| `PHP_SURFACE_MAX_OUTPUT_BYTES` | `8192` | Maximum stdout size in bytes before exit code `3`. Invalid or non-positive values fall back to `8192`. |

Examples:

```bash
PHP_SURFACE_BIN_PATH=/usr/bin/php8.3 php-surface /absolute/path/to/File.php

PHP_SURFACE_MAX_OUTPUT_BYTES=32000 php-surface /absolute/path/to/Large.php
```

The wrapper script (`bin/php-surface`) reads `PHP_SURFACE_BIN_PATH`. The application reads `PHP_SURFACE_MAX_OUTPUT_BYTES` via `OutputGuard`.

## Output modes at a glance

| Mode | Flag | stdout | Typical use |
|------|------|--------|-------------|
| Symbol map | _(default)_ | JSON or `--text` | Full structural overview |
| Summary | `--stats` | JSON or `--text` | Large files — counts and top symbols |
| Search | `--search <term>` | JSON or `--text` | Find symbols/methods/lines by keyword |
| Method body | `--show <symbol>` | JSON or `--text` | Read one implementation |

### Combinable flags

| Flag | Combines with |
|------|----------------|
| `--text` | Any mode |
| `--full` | Default map only |
| `--filter` | Default map, `--stats` |
| `--visibility` | Default map, `--stats`, `--search` |
| `--allow-large-output` | Any mode that emits stdout |

## Incremental exploration workflow

Large PHP files often exceed the 8 KB output guard. Explore in layers instead of dumping everything at once.

!!! tip "Recommended sequence"
    1. **`--stats`** — file size, symbol counts, largest classes by method count  
    2. **`--filter`** or **`--search`** — narrow to relevant methods  
    3. **`--show`** — read a single method body when needed  

```bash
# 1. Overview
php-surface /absolute/path/to/Large.php --stats --text

# 2. Narrow
php-surface /absolute/path/to/Large.php --filter save
php-surface /absolute/path/to/Large.php --search repository

# 3. Drill down
php-surface /absolute/path/to/Large.php --show OrderService::save
```

If you hit [exit code `3`](exit-codes.md#3-output-too-large), stderr includes suggested commands — prefer those over `--allow-large-output` unless you truly need the full output.

## Default JSON shape (brief)

The default map emits sparse JSON: empty keys are omitted. Each symbol includes `name`, `namespace`, `type`, `line`, and `methods` (when present). Methods include `name`, `visibility`, `signature`, `startLine`, `endLine`, and optional fields such as `returnType`, `modifiers`, and docblock summaries when `--full` is set.

See [Output Examples](examples.md) for full sample output.

## Related

- [Exit codes](exit-codes.md) — `0`–`3` and stderr examples
- [Output examples](examples.md) — JSON and `--text` samples
- [Quick Start](../getting-started/quickstart.md) — first commands
