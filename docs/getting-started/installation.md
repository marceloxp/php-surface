# Installation

## Requirements

- **PHP 8.3+** — runtime for the CLI (static analysis only; your project's PHP version does not matter)
- **Composer** — to install dependencies

## Clone and install

```bash
git clone https://github.com/marceloxp/php-surface.git
cd php-surface
composer install
```

Verify the CLI:

```bash
./bin/php-surface --version
```

## Make `php-surface` invocable from anywhere

Pick one approach below (replace `/path/to/php-surface` with your clone path).

### Add `bin` to your PATH (recommended)

```bash
# Current shell session
export PATH="/path/to/php-surface/bin:$PATH"
php-surface --version
```

To make it permanent, add the same `export` line to your shell profile (`~/.bashrc`, `~/.zshrc`, etc.), then reload the shell.

### Symlink into a directory already on PATH

If a directory such as `~/.local/bin` is already on your `PATH` (common in IDE agent shells), you can symlink the wrapper once:

```bash
ln -sf /path/to/php-surface/bin/php-surface ~/.local/bin/php-surface
php-surface --version
```

The wrapper resolves symlinks to the real script, so the project root is found correctly regardless of where the symlink lives.

### Full path (no PATH change)

```bash
/path/to/php-surface/bin/php-surface /absolute/path/to/File.php
```

### After setup

Once `php-surface` is on your `PATH` (via export or symlink), run:

```bash
php-surface /absolute/path/to/File.php
```

## File path argument

The `<file.php>` argument must point to the PHP file you want to analyze. **Use the full (absolute) path** — for example:

```bash
php-surface /var/www/my-app/src/Service/OrderService.php
```

Relative paths work only when they resolve correctly from your current working directory; absolute paths avoid ambiguity.

## PHP binary selection

By default, `bin/php-surface` uses the first compatible PHP 8.3+ binary found in your `PATH`.

To pin a specific binary:

```bash
PHP_SURFACE_BIN_PATH=/usr/bin/php8.3 php-surface /absolute/path/to/File.php
```

## Project layout

After install, the entry point is:

```
bin/php-surface    # wrapper script
src/               # application code
vendor/            # Composer dependencies
```

## Development dependencies

To run tests:

```bash
composer test
```

See the [GitHub repository](https://github.com/marceloxp/php-surface) for contribution guidelines and CI setup.
