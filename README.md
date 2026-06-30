# php-surface

Structural explorer for PHP source files. Outputs a compact map of classes, traits, and interfaces for incremental exploration.

**Documentation → [marceloxp.github.io/php-surface](https://marceloxp.github.io/php-surface/)**

## Requirements

- PHP 8.3+
- [Composer](https://getcomposer.org/)

## Install

```bash
git clone https://github.com/marceloxp/php-surface.git
cd php-surface
composer install
export PATH="/path/to/php-surface/bin:$PATH"
```

## Example

```bash
php-surface /absolute/path/to/File.php --stats --text
```

Default output is JSON. Use `--text` for a human-readable map. Always pass the **full path** to the `.php` file.

## Tests

```bash
composer test
```

Full CLI reference, output examples, exit codes, and AI agent integration are in the [documentation site](https://marceloxp.github.io/php-surface/).
