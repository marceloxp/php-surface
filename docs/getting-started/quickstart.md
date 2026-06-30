# Quick Start

Analyze a PHP file and print its structural map. Pass the **full path** to the file:

```bash
php-surface /absolute/path/to/File.php
```

If `bin` is not on your `PATH`, use the wrapper explicitly:

```bash
/path/to/php-surface/bin/php-surface /absolute/path/to/File.php
```

Default output is **JSON** on stdout. For a readable map:

```bash
php-surface /absolute/path/to/File.php --text
```

## Explore incrementally

For large files, start with a summary instead of the full map:

```bash
php-surface /absolute/path/to/Large.php --stats --text
```

Narrow to methods whose name contains a substring:

```bash
php-surface /absolute/path/to/File.php --filter save
```

Read one method's source when you need implementation detail:

```bash
php-surface /absolute/path/to/File.php --show ClassName::save
```

Search symbols, methods, and source lines (case-insensitive):

```bash
php-surface /absolute/path/to/File.php --search nested
```

## Recommended workflow

1. **`--stats`** — decide whether the file is worth exploring
2. **`--filter`** or **`--search`** — focus on relevant symbols
3. **`--show`** — read only the method body you need

!!! tip "AI agents"
    Prefer JSON output (default) and use `--stats` before requesting a full symbol map. If exit code is `3`, output exceeded the size guard — use filters instead of `--allow-large-output` unless you truly need the full map.

## Help and version

```bash
php-surface --help
php-surface --version
```

## What's next

- [Installation](installation.md) — PATH, symlink, or full-path setup
- [Commands reference](../cli/reference.md) — all flags and options
- [Output examples](../cli/examples.md) — sample JSON and text output
- [Exit codes](../cli/exit-codes.md) — meaning of each exit code
