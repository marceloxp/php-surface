# Exit Codes

`php-surface` uses a small, stable set of exit codes. Errors are written to **stderr**; successful output goes to **stdout**.

## Summary

| Code | Name | Meaning |
|------|------|---------|
| `0` | Success | Output written to stdout |
| `1` | Usage | Invalid invocation or symbol not found |
| `2` | File error | Missing file, wrong extension, or parse failure |
| `3` | Output too large | stdout would exceed the size guard |

## 0 — Success

The command completed and printed results to stdout.

```bash
php-surface /absolute/path/to/File.php
echo $?   # 0
```

Also returned for `--help` and `--version`.

## 1 — Usage error

Invalid arguments, missing required values, unknown options, or `--show` target not found.

### Missing file argument

```bash
php-surface
```

```text
Error: missing required argument <file.php>
```

### Option requires a value

```bash
php-surface /absolute/path/to/File.php --filter
```

```text
Error: --filter requires a value
```

Same pattern for `--visibility`, `--show`, and `--search` when the flag is present without a value.

### Invalid visibility

```bash
php-surface /absolute/path/to/File.php --visibility invalid
```

```text
Error: --visibility must be one of: public, protected, private
```

### Symbol not found (`--show`)

```bash
php-surface /absolute/path/to/File.php --show nonexistent
```

```text
Error: no symbol matched "nonexistent"
```

## 2 — File error

The path is not a readable `.php` file, or parsing failed.

### Not a PHP file

```bash
php-surface /absolute/path/to/README.md
```

```text
Error: "/absolute/path/to/README.md" is not a PHP file (.php extension required)
```

### File not found

```bash
php-surface /absolute/path/to/missing.php
```

```text
Error: file not found or not readable: /absolute/path/to/missing.php
```

### Parse error

```bash
php-surface /absolute/path/to/SyntaxError.php
```

```text
Error: Syntax error, unexpected ...
```

(parse message comes from the PHP parser)

## 3 — Output too large

Rendered stdout would exceed `PHP_SURFACE_MAX_OUTPUT_BYTES` (default **8192**). Nothing is written to stdout. stderr contains an error payload and **hints** for narrower commands.

### JSON (default)

```bash
php-surface /absolute/path/to/Large.php
```

stderr (example structure):

```json
{
  "error": "output_too_large",
  "file": "/absolute/path/to/Large.php",
  "fileBytes": 70431,
  "outputBytes": 42156,
  "limitBytes": 8192,
  "hints": [
    "php-surface /absolute/path/to/Large.php --stats",
    "php-surface /absolute/path/to/Large.php --search <term>",
    "php-surface /absolute/path/to/Large.php --visibility public",
    "php-surface /absolute/path/to/Large.php --filter save",
    "php-surface /absolute/path/to/Large.php --show ClassName::method",
    "php-surface /absolute/path/to/Large.php --allow-large-output"
  ]
}
```

Hint commands use the file path you passed; exact methods depend on the file's symbols.

### Text (`--text`)

```bash
php-surface /absolute/path/to/Large.php --text
```

stderr (example):

```text
Error: output too large (12345 bytes, limit 8192).
File: /absolute/path/to/Large.php (70431 bytes)

Narrow the exploration instead of dumping the full output:

  php-surface /absolute/path/to/Large.php --stats
  php-surface /absolute/path/to/Large.php --search <term>
  ...
```

### Remediation

| Approach | When |
|----------|------|
| `--stats` | First look at large files |
| `--filter` / `--search` / `--visibility` | Narrow before full map |
| `--show` | Need one method only |
| `--allow-large-output` | You intentionally need everything |
| Raise `PHP_SURFACE_MAX_OUTPUT_BYTES` | Controlled environments with larger context budgets |

!!! warning "AI agents"
    Exit code `3` means the full map was **not** printed. Follow the hints on stderr instead of retrying the same command or defaulting to `--allow-large-output`.

## Related

- [Commands](reference.md) — flags, env vars, exploration workflow
- [Output examples](examples.md) — successful stdout samples
