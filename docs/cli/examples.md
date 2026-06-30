# Output Examples

Real command invocations and stdout/stderr samples. Examples use fixtures from `tests/fixtures/` in your clone — invoke `php-surface` with **absolute paths** after [making the CLI invocable](../getting-started/installation.md#make-php-surface-invocable-from-anywhere) (replace `/path/to/php-surface` with your clone location).

Sample stdout below matches snapshot tests (relative `file` paths in JSON). When you pass an absolute path, the `file` field and the first line of `--text` output echo the path you supplied.

!!! note "Sparse JSON"
    Default JSON omits empty keys. Methods without docblocks have no `docblock` field; methods without modifiers have no `modifiers` array. See the [Commands](reference.md) reference for the full schema.

!!! tip "Large files"
    On big PHP files, start with `--stats` before requesting the full symbol map. The [output size guard](reference.md#output-size) blocks oversized stdout unless you narrow the query or pass `--allow-large-output`.

## Fixtures

| Fixture | Purpose |
|---------|---------|
| `Methods.php` | Class with public/protected methods, `static`, `final`, and `abstract` modifiers |
| `Docblocks.php` | Docblocks with summaries, `@return`, and `@throws` |
| `Monster.php` | Large file (~70 KB) for `--stats` and output guard demos |
| `Empty.php` | PHP file with no symbols |

---

## Default structural map

Prints every symbol and method signature in the file.

```bash
php-surface /path/to/php-surface/tests/fixtures/Methods.php
```

=== "JSON"

    ```json
    {
        "file": "tests/fixtures/Methods.php",
        "symbols": [
            {
                "name": "OrderService",
                "namespace": "App\\Repo",
                "type": "class",
                "line": 7,
                "methods": [
                    {
                        "name": "save",
                        "visibility": "public",
                        "signature": "public function save(Order $order): void",
                        "startLine": 9,
                        "endLine": 11,
                        "returnType": "void"
                    },
                    {
                        "name": "findActive",
                        "visibility": "protected",
                        "signature": "protected static function findActive(int $companyId): array",
                        "startLine": 13,
                        "endLine": 16,
                        "returnType": "array",
                        "modifiers": ["static"]
                    },
                    {
                        "name": "lock",
                        "visibility": "public",
                        "signature": "final public function lock(): bool",
                        "startLine": 18,
                        "endLine": 21,
                        "returnType": "bool",
                        "modifiers": ["final"]
                    },
                    {
                        "name": "resolve",
                        "visibility": "protected",
                        "signature": "abstract protected function resolve(): mixed",
                        "startLine": 23,
                        "endLine": 23,
                        "returnType": "mixed",
                        "modifiers": ["abstract"]
                    }
                ]
            }
        ]
    }
    ```

=== "Text (`--text`)"

    ```bash
    php-surface /path/to/php-surface/tests/fixtures/Methods.php --text
    ```

    ```text
    tests/fixtures/Methods.php

    class App\Repo\OrderService (line 7)
      public function save(Order $order): void
      protected static function findActive(int $companyId): array
      final public function lock(): bool
      abstract protected function resolve(): mixed
    ```

---

## Filter by method name

Keeps only methods whose name contains the filter term (case-insensitive). Symbols with no matching methods are dropped.

```bash
php-surface /path/to/php-surface/tests/fixtures/Methods.php --filter find
```

```json
{
    "file": "tests/fixtures/Methods.php",
    "symbols": [
        {
            "name": "OrderService",
            "namespace": "App\\Repo",
            "type": "class",
            "line": 7,
            "methods": [
                {
                    "name": "findActive",
                    "visibility": "protected",
                    "signature": "protected static function findActive(int $companyId): array",
                    "startLine": 13,
                    "endLine": 16,
                    "returnType": "array",
                    "modifiers": ["static"]
                }
            ]
        }
    ]
}
```

---

## Filter by visibility

Shows only methods at the requested visibility level.

```bash
php-surface /path/to/php-surface/tests/fixtures/Methods.php --visibility public --text
```

```text
tests/fixtures/Methods.php

class App\Repo\OrderService (line 7)
  public function save(Order $order): void
  final public function lock(): bool
```

---

## Show method source

Extracts the source body for a single method. Use a bare method name or `ClassName::method`.

```bash
php-surface /path/to/php-surface/tests/fixtures/Methods.php --show save
```

```json
{
    "file": "tests/fixtures/Methods.php",
    "matches": [
        {
            "symbol": "OrderService",
            "namespace": "App\\Repo",
            "type": "class",
            "method": "save",
            "startLine": 9,
            "endLine": 11,
            "source": "    public function save(Order $order): void\n    {\n    }\n"
        }
    ]
}
```

---

## Stats summary

Compact overview for large files — symbol counts, visibility breakdown, and the largest classes by method count.

```bash
php-surface /path/to/php-surface/tests/fixtures/Monster.php --stats --text
```

```text
tests/fixtures/Monster.php
70431 bytes, 2726 lines

Symbols: 61 (42 classes, 11 interfaces, 8 traits)
Methods: 210 (199 public, 4 protected, 7 private)
Docblocks: 37 methods with summary

Largest by methods:
  class Surface\Monster\User — 21 methods (line 254)
  class Surface\Monster\ComprehensivePhpDocExample — 12 methods (line 2491)
  class Surface\Monster\GenericCollection — 11 methods (line 1862)
  class Surface\Monster\MagicMethodsExample — 10 methods (line 714)
  class Surface\Monster\ShortMethodExample — 10 methods (line 1201)
  class Surface\Monster\TypeDeclarationExample — 9 methods (line 1628)
  class Surface\Monster\ReturnTypeExample — 8 methods (line 2150)
  class Surface\Monster\ComplexMethodExample — 7 methods (line 630)
  class Surface\Monster\DynamicPropertiesExample — 6 methods (line 2266)
  class Surface\Monster\FileLevelTestClass — 6 methods (line 2662)
```

---

## Full mode (parameters)

Adds structured `parameters` arrays to each method. Combine with `--filter` or `--visibility` on large files to stay within the output limit.

```bash
php-surface /path/to/php-surface/tests/fixtures/Methods.php --full
```

```json
{
    "file": "tests/fixtures/Methods.php",
    "symbols": [
        {
            "name": "OrderService",
            "namespace": "App\\Repo",
            "type": "class",
            "line": 7,
            "methods": [
                {
                    "name": "save",
                    "visibility": "public",
                    "signature": "public function save(Order $order): void",
                    "startLine": 9,
                    "endLine": 11,
                    "returnType": "void",
                    "parameters": [
                        {
                            "name": "order",
                            "type": "Order"
                        }
                    ]
                },
                {
                    "name": "findActive",
                    "visibility": "protected",
                    "signature": "protected static function findActive(int $companyId): array",
                    "startLine": 13,
                    "endLine": 16,
                    "returnType": "array",
                    "modifiers": ["static"],
                    "parameters": [
                        {
                            "name": "companyId",
                            "type": "int"
                        }
                    ]
                },
                {
                    "name": "lock",
                    "visibility": "public",
                    "signature": "final public function lock(): bool",
                    "startLine": 18,
                    "endLine": 21,
                    "returnType": "bool",
                    "modifiers": ["final"]
                },
                {
                    "name": "resolve",
                    "visibility": "protected",
                    "signature": "abstract protected function resolve(): mixed",
                    "startLine": 23,
                    "endLine": 23,
                    "returnType": "mixed",
                    "modifiers": ["abstract"]
                }
            ]
        }
    ]
}
```

---

## Full mode (docblocks)

On files with documentation, `--full` includes the complete docblock text plus parsed tags.

```bash
php-surface /path/to/php-surface/tests/fixtures/Docblocks.php --full
```

```json
{
    "file": "tests/fixtures/Docblocks.php",
    "symbols": [
        {
            "name": "PaymentGateway",
            "namespace": "App\\Svc",
            "type": "class",
            "line": 7,
            "methods": [
                {
                    "name": "charge",
                    "visibility": "public",
                    "signature": "public function charge(Money $amount): string",
                    "startLine": 10,
                    "endLine": 12,
                    "returnType": "string",
                    "docblock": {
                        "text": "/** Processa pagamento e retorna ID da transação. */"
                    },
                    "parameters": [
                        {
                            "name": "amount",
                            "type": "Money"
                        }
                    ]
                },
                {
                    "name": "refund",
                    "visibility": "public",
                    "signature": "public function refund(string $id): void",
                    "startLine": 14,
                    "endLine": 16,
                    "returnType": "void",
                    "parameters": [
                        {
                            "name": "id",
                            "type": "string"
                        }
                    ]
                },
                {
                    "name": "pending",
                    "visibility": "protected",
                    "signature": "protected function pending(): array",
                    "startLine": 24,
                    "endLine": 27,
                    "returnType": "array",
                    "docblock": {
                        "text": "/**\n     * Lista cobranças pendentes.\n     *\n     * @return array<int, Charge>\n     * @throws GatewayException quando o provedor está indisponível\n     */",
                        "return": "array<int, Charge>",
                        "throws": [
                            "GatewayException quando o provedor está indisponível"
                        ]
                    }
                }
            ]
        }
    ]
}
```

---

## Empty file

A valid PHP file with no classes, traits, or interfaces returns an empty `symbols` array.

```bash
php-surface /path/to/php-surface/tests/fixtures/Empty.php
```

```json
{
    "file": "tests/fixtures/Empty.php",
    "symbols": []
}
```

---

## Output blocked (exit 3)

When stdout would exceed the size guard (default 8192 bytes), the CLI exits with code `3`, writes nothing to stdout, and prints remediation hints to stderr. See [Exit codes](exit-codes.md#3-output-too-large) for details.

```bash
php-surface /path/to/php-surface/tests/fixtures/Monster.php
echo $?   # 3
```

=== "JSON (default)"

    ```json
    {
        "error": "output_too_large",
        "file": "tests/fixtures/Monster.php",
        "fileBytes": 70431,
        "outputBytes": 42192,
        "limitBytes": 8192,
        "hints": [
            "php-surface /path/to/php-surface/tests/fixtures/Monster.php --stats",
            "php-surface /path/to/php-surface/tests/fixtures/Monster.php --search <term>",
            "php-surface /path/to/php-surface/tests/fixtures/Monster.php --visibility public",
            "php-surface /path/to/php-surface/tests/fixtures/Monster.php --filter __construct",
            "php-surface /path/to/php-surface/tests/fixtures/Monster.php --show User::__construct",
            "php-surface /path/to/php-surface/tests/fixtures/Monster.php --allow-large-output"
        ]
    }
    ```

=== "Text (`--text`)"

    When `--text` is set, stderr uses a human-readable message instead of JSON:

    ```bash
    php-surface /path/to/php-surface/tests/fixtures/Monster.php --text
    ```

    ```text
    Error: output too large (14793 bytes, limit 8192).
    File: tests/fixtures/Monster.php (70431 bytes)

    Narrow the exploration instead of dumping the full output:

      php-surface /path/to/php-surface/tests/fixtures/Monster.php --stats
      php-surface /path/to/php-surface/tests/fixtures/Monster.php --search <term>
      php-surface /path/to/php-surface/tests/fixtures/Monster.php --visibility public
      php-surface /path/to/php-surface/tests/fixtures/Monster.php --filter __construct
      php-surface /path/to/php-surface/tests/fixtures/Monster.php --show User::__construct
      php-surface /path/to/php-surface/tests/fixtures/Monster.php --allow-large-output
    ```

Follow any hint from stderr, or pass `--allow-large-output` when you intentionally need the full map.

---

## Maintaining these examples

Golden output lives in `tests/.pest/snapshots/Feature/CliSnapshotTest/`. When CLI output changes intentionally:

1. Run `./vendor/bin/pest --update-snapshots`
2. Update this page to match the new snapshots
3. Mention snapshot ↔ docs alignment in the PR description
