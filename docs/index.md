# php-surface

**Structural explorer for PHP source files.** Outputs a compact map of classes, traits, and interfaces so you can explore code incrementally — without reading every line first.

[:octicons-arrow-right-24: Quick Start](getting-started/quickstart.md){ .md-button .md-button--primary }
[:octicons-mark-github-16: GitHub](https://github.com/marceloxp/php-surface){ .md-button }

## The problem

Large PHP files often contain hundreds or thousands of lines while exposing a relatively small public surface. AI agents and developers typically read the entire file before deciding which methods deserve deeper inspection — wasting context, tokens, and time on every large file.

## The solution

`php-surface` extracts a **deterministic structural map** from a single `.php` file: namespaces, symbol types, method signatures, visibility, return types, and line numbers. When you need implementation details, drill down with `--show` — one method at a time.

The analyzed code is **never executed**. Everything comes from static parsing.

## Quick example

```bash
./bin/php-surface /path/to/php-surface/tests/fixtures/Methods.php --text
```

```text
/path/to/php-surface/tests/fixtures/Methods.php

class App\Repo\OrderService (line 7)
  public function save(Order $order): void
  protected static function findActive(int $companyId): array
  final public function lock(): bool
  abstract protected function resolve(): mixed
```

Default output is JSON. Use `--text` for human-readable maps like the one above. Always pass the **full path** to the `.php` file (see [Installation](getting-started/installation.md)).

## Features

- **Offline & deterministic** — same input always produces identical output; no network, no side effects
- **JSON-first** — canonical format for AI agents; `--text` for humans
- **Incremental exploration** — `--stats`, `--filter`, `--search`, and `--show` narrow focus before reading source
- **Output size guard** — default 8 KB cap keeps agent context lean; hints when output is too large
- **Sparse JSON** — omits empty keys; no placeholder skeletons on undocumented methods

## Next steps

- [Install php-surface](getting-started/installation.md) — PHP 8.3+ and Composer
- [Quick Start](getting-started/quickstart.md) — first commands and exploration workflow
- [CLI Reference](cli/reference.md) — full flag reference *(coming in Phase 2)*
