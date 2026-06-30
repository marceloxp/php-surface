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

## Add `bin` to your PATH

To invoke `php-surface` from any directory without typing the full path to the wrapper:

```bash
# Current shell session (replace with your clone path)
export PATH="/path/to/php-surface/bin:$PATH"
php-surface --version
```

To make it permanent, add the same `export` line to your shell profile (`~/.bashrc`, `~/.zshrc`, etc.), then reload the shell.

After adding to `PATH`, you can run:

```bash
php-surface /absolute/path/to/File.php
```

If you prefer not to modify `PATH`, use the wrapper directly with a full path:

```bash
/path/to/php-surface/bin/php-surface /absolute/path/to/File.php
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
