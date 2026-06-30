# AI agents

Configure **php-surface** once on your machine so coding agents — Cursor, Claude Code, Codex, and similar tools — explore large PHP files incrementally: structural map first, full source only when needed.

This guide assumes a **global install**: one clone on the developer machine, invocable from any project directory. Per-project copies are optional and not required.

For flag details and sample output, see [Commands](../cli/reference.md) and [Output Examples](../cli/examples.md).

## Prerequisites

| Requirement | Notes |
|-------------|-------|
| **PHP 8.3+** | Runtime for php-surface only; your app's PHP version does not matter |
| **Composer** | To install php-surface dependencies |
| **Git** | To clone the repository |
| **Coding agent with shell access** | Agent must be able to run terminal commands (Bash tool, shell mode, etc.) |

php-surface is **not on Packagist yet** — install from the GitHub repository.

## One-time machine setup

Clone php-surface once anywhere on your machine (for example `~/tools/php-surface` or `~/git/php-surface`):

```bash
git clone https://github.com/marceloxp/php-surface.git ~/tools/php-surface
cd ~/tools/php-surface
composer install --no-dev
./bin/php-surface --version
# php-surface 0.1.0-dev
```

Then make the CLI invocable from any directory. Pick one approach (replace `/path/to/php-surface` with your clone path):

| Approach | Setup | Agent runs |
|----------|-------|------------|
| **PATH (recommended)** | Add to shell profile: `export PATH="/path/to/php-surface/bin:$PATH"` | `php-surface /absolute/path/to/File.php` |
| **Symlink** | When `~/.local/bin` is already on PATH: `ln -sf /path/to/php-surface/bin/php-surface ~/.local/bin/php-surface` | `php-surface /absolute/path/to/File.php` |
| **Full path** | No PATH change — document the full wrapper path in agent rules | `/path/to/php-surface/bin/php-surface /absolute/path/to/File.php` |
| **PHP binary override** | When default `php` is &lt; 8.3 | `PHP_SURFACE_BIN_PATH=/usr/bin/php8.3 php-surface ...` |

Full details (symlink behavior, permanent PATH, PHP binary selection): [Installation](../getting-started/installation.md#make-php-surface-invocable-from-anywhere).

Confirm from any directory:

```bash
php-surface --version
php-surface /absolute/path/to/your-project/src/Service/OrderService.php --stats
```

Always pass the **absolute path** to the PHP file under analysis. Relative paths depend on the agent's current working directory and often fail silently or resolve to the wrong file.

## Agent rules snippet

Paste the block below into your project's agent instructions — `CLAUDE.md`, `AGENTS.md`, `.cursor/rules`, or your editor's equivalent. Adjust only if you use a **full-path** invocation instead of PATH.

````markdown
## PHP structural exploration (php-surface)

Before using the Read tool on a PHP file larger than ~200 lines, explore it with php-surface:

1. `php-surface /absolute/path/to/File.php --stats` — file size, symbol counts, largest classes
2. `php-surface /absolute/path/to/File.php --filter <term>` or `--search <term>` — narrow to relevant methods
3. `php-surface /absolute/path/to/File.php --show ClassName::method` — read one method body only when needed

Rules:

- Default output is JSON (preferred for agents). Use `--text` only when a human-readable map is requested.
- Always pass the **full absolute path** to the `.php` file.
- If exit code is **3**, output exceeded the 8 KB guard — follow stderr hints (`--stats`, `--filter`, `--search`, `--visibility`). Do **not** use `--allow-large-output` unless the task truly requires the full symbol map.
- `php-surface` is installed globally on this machine and available on PATH.

Do not read the entire PHP file when a structural map or `--show` excerpt is sufficient.
````

## Shell permissions

Agents run php-surface through their **shell** or **Bash** tool. On first run you may be prompted to approve the command — allow it for the session or add a persistent allow rule.

Practical tips:

- **Run a manual smoke test** before relying on the agent:  
  `php-surface /absolute/path/to/a/large/file.php --stats`
- **Keep the install path stable** — moving the clone breaks allowlists and documented paths.
- **Command allowlists** — if your agent supports safe-command rules, allow `php-surface` (or the full path to `bin/php-surface`) as a read-only analysis command. See your editor's documentation for its permission model.

php-surface only **reads and parses** PHP source; it never executes the analyzed code.

## Incremental workflow

```mermaid
flowchart TD
    A[Agent receives task involving Large.php] --> B["php-surface Large.php --stats"]
    B --> C{Small surface?}
    C -->|Yes| D["php-surface Large.php --show Target::method"]
    C -->|No| E["--filter / --search / --visibility public"]
    E --> F["php-surface Large.php --show Target::method"]
    D --> G[Use extracted source lines only]
    F --> G
```

Human-readable version:

1. **`--stats`** — decide whether the file is worth exploring and identify large classes.
2. **`--filter` / `--search` / `--visibility`** — shrink the symbol map to what matters for the task.
3. **`--show`** — pull source for one method when implementation detail is required.

Avoid opening the full file in the Read tool until steps 1–3 show that broader context is necessary.

## Environment variables

| Variable | Use with agents |
|----------|-----------------|
| `PHP_SURFACE_BIN_PATH` | Point the wrapper at PHP 8.3+ when `php` on `PATH` is too old (common in mixed-version environments). |
| `PHP_SURFACE_MAX_OUTPUT_BYTES` | Raise the stdout cap (default `8192`) only if the agent's context window can absorb it — prefer filters first. |

Example for a pinned PHP binary:

```bash
export PHP_SURFACE_BIN_PATH=/usr/bin/php8.3
php-surface /absolute/path/to/File.php --stats
```

See [Commands → Environment variables](../cli/reference.md#environment-variables) for defaults and behavior.

## Troubleshooting

| Problem | What to check |
|---------|----------------|
| **`command not found: php-surface`** | `bin` is not on PATH in the shell the agent uses. Fix: [add `bin` to PATH or symlink](../getting-started/installation.md#make-php-surface-invocable-from-anywhere), or document the full path to `bin/php-surface` in agent rules. |
| **Exit code `2` — parse error** | The target file has invalid PHP syntax. Fix the source file; this is not a php-surface bug. |
| **Exit code `3` — output too large** | Full map exceeds the guard. Run `--stats`, then `--filter` / `--search` / `--visibility` per stderr hints. Avoid `--allow-large-output` unless you intentionally need the entire map. See [Exit codes](../cli/exit-codes.md#3-output-too-large). |
| **Agent reads the whole file anyway** | Strengthen agent rules: require php-surface **before** Read on files over N lines; mention exit code `3` handling explicitly. |
| **Wrong file or empty output** | Agent used a relative path from the wrong cwd. Require **absolute paths** in agent rules. |
| **PHP version error from wrapper** | Set `PHP_SURFACE_BIN_PATH` to a PHP 8.3+ binary. |

## Optional: agent skill (advanced)

You can wrap this workflow in a **skill** (Cursor, Claude Code, or similar) with triggers like "explore PHP file" or "php surface map". Skills live in editor-specific directories (for example `.cursor/skills/`) and encode the `--stats` → `--filter` / `--search` → `--show` sequence so agents do not rely on memory alone.

This is optional — the agent rules snippet above is enough for most projects. See your editor's skill documentation when you want reusable, trigger-based automation.

## Related

- [Installation](../getting-started/installation.md) — global PATH, symlink, and PHP setup
- [Quick Start](../getting-started/quickstart.md) — first commands
- [Commands](../cli/reference.md) — all flags and options
- [Output Examples](../cli/examples.md) — JSON and `--text` samples
- [Exit codes](../cli/exit-codes.md) — codes 0–3
