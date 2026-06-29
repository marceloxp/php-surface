# Proposal: php-surface — Structural Explorer for PHP Source Files

## Summary

**php-surface** is an **offline, deterministic structural explorer** for PHP source files.

Instead of forcing an AI agent or developer to read hundreds or thousands of lines just to understand a file's public surface, `php-surface` extracts a compact structural representation of its symbols.

For each class, trait and interface, it exposes its API surface: methods grouped by visibility, complete signatures, summarized documentation, return contract, exception contract and source location. When more detail is needed, individual symbols can be explored on demand.

The tool never executes the analyzed code.

Its primary goal is to help AI agents decide **what they actually need to read**, dramatically reducing context consumption while navigating large PHP codebases.

---

# Problem

Large PHP files frequently contain hundreds or thousands of lines while exposing only a relatively small public surface.

For example:

* 1,179-line trait
* ~30 helper methods
* ~40 lines of information actually needed to understand the available API

Today an AI agent typically reads the entire file before deciding which methods deserve deeper inspection.

This wastes:

* context window
* tokens
* execution time

and repeats for every large file explored.

---

# Goals

Provide a deterministic structural representation of a PHP source file that allows AI agents and developers to explore code incrementally instead of reading everything.

The output should contain enough information to answer:

> "Do I actually need to open this method?"

without exposing its implementation.

---

# Design Principles

## Structural exploration

`php-surface` is a navigation tool, not a documentation generator.

It exposes only the information necessary to decide the next exploration step.

---

## Static analysis only

The analyzed code is **never executed**.

The tool:

* never includes files
* never requires files
* never executes autoloaders
* never instantiates project classes
* never connects to external resources

Everything is extracted through static parsing.

---

## Deterministic

Given the same input file, the output must always be identical.
Ordering is stable.
Formatting is stable.
No timestamps.
No randomness.

This makes the output cacheable and friendly to AI workflows.

---

## AI-first

The canonical output format is JSON.
Human-readable text is provided as an alternative presentation layer.

---

## Sparse JSON

The default JSON output is **sparse by omission**:

* omit keys whose value would be empty, null or false-by-default
* one canonical source per datum — avoid duplicating the same information in multiple shapes
* never emit placeholder skeletons (e.g. empty docblock objects on undocumented methods)

Human-readable `--text` output is unaffected; it may repeat information for readability.

---

# Scope

Given a PHP file, extract:

For every class, trait or interface:

* name
* namespace
* type (class, trait, interface)
* line where declaration begins

For every method (default JSON):

* name
* visibility
* `modifiers` — only when non-empty: any of `static`, `final`, `abstract`
* complete signature (always present; canonical human/agent-readable form)
* return type declaration (always present when declared; omitted when absent)
* `startLine` and `endLine` (line range for drill-down via `--show`)
* docblock — only when a docblock exists; inside it, omit keys with no value

Docblock fields (when present):

* summary (first meaningful paragraph)
* `@return` text
* `@throws` list

With `--full`, additionally include per method:

* complete docblock text (instead of summary only)
* structured `parameters`: name, type, default value

Parameter types and defaults are always encoded in `signature`; structured `parameters` are an opt-in expansion, not part of the default map.

---

# Commands

## Surface

```bash
php-surface Foo.php
```

Outputs the structural representation in JSON.

---

## Human-readable output

```bash
php-surface Foo.php --text
```

Produces a compact text representation for terminal use.

---

## Filter methods

```bash
php-surface Foo.php --filter save
```

---

## Filter by visibility

```bash
php-surface Foo.php --visibility public
```

---

## Show symbol implementation

```bash
php-surface Foo.php --show save
```

If multiple symbols match, all matches are returned.

The consumer (human or AI) decides which one should be explored next.

The caller can then request the desired symbol explicitly:

php-surface Foo.php --show UserRepository::save

---

## Full detail

```bash
php-surface Foo.php --full
```

Expands the default JSON map:

* complete docblock text instead of summary only
* structured `parameters` (name, type, default) per method

---

# Output

Canonical output:

* JSON (sparse by default)

Alternative output:

* text (`--text`)

The JSON structure is considered the public interface of the tool.

## JSON shape (default)

```json
{
  "file": "Foo.php",
  "symbols": [
    {
      "name": "UserRepository",
      "namespace": "App\\Repository",
      "type": "class",
      "line": 10,
      "methods": [
        {
          "name": "save",
          "visibility": "public",
          "signature": "public function save(User $user): void",
          "returnType": "void",
          "startLine": 42,
          "endLine": 59
        },
        {
          "name": "findActive",
          "visibility": "protected",
          "modifiers": ["static"],
          "signature": "protected static function findActive(int $companyId): array",
          "returnType": "array",
          "docblock": {
            "summary": "lojas ativas da empresa",
            "return": "[id => nome]"
          },
          "startLine": 61,
          "endLine": 88
        }
      ]
    }
  ]
}
```

### Field rules

| Field | Rule |
|-------|------|
| `signature` | Always present. Single canonical string: visibility, modifiers, name, params, return. |
| `returnType` | Present when declared. Omitted when the method has no return type. Cheap to read without parsing `signature`. |
| `modifiers` | Array of `static`, `final`, `abstract`. **Omitted when empty** — not three `false` booleans. |
| `docblock` | **Omitted when no docblock.** Inside it, omit `summary`, `return`, `throws` when absent — no null placeholders. |
| `parameters` | **Only with `--full`.** Default map relies on `signature`. |
| `startLine` / `endLine` | Always present. Line range used directly by `--show`; preferred over `lineCount`. |

`save` carries no `modifiers` and no `docblock` because there is nothing to say. `findActive` carries only the docblock keys that exist.

---

# Runtime

`php-surface` requires PHP 8.1+ to execute.

The analyzed project may target any PHP version supported by the parser.

The runtime PHP can be configured through:

```bash
PHP_SURFACE_BIN_PATH=/opt/php/8.3/bin/php
```

If omitted, `php-surface` falls back to the first compatible `php` available in the user's `PATH`.

---

# Technology

Implementation language:

* PHP

Parser library:

* voku/simple-php-code-parser

The library already provides a high-level representation of PHP symbols while integrating modern PHPDoc parsing, significantly reducing implementation complexity.

---

# Non-goals

The following are intentionally outside the scope of the project:

* repository-wide indexing
* semantic search
* natural language queries
* code generation
* docblock generation
* executing analyzed code
* dependency analysis
* call graph generation
* cross-file navigation
* IDE replacement

---

# Development

Incremental, testable delivery phases are documented in [`ROADMAP.md`](ROADMAP.md).

Each phase delivers one verifiable capability: develop → manual test → approve → next phase.

Post-MVP ideas (repository index, cross-file resolution, call graphs, LSP) remain out of scope until the MVP is validated.
