<?php

declare(strict_types=1);

namespace PhpSurface\Cli;

use PhpSurface\Extractor\SymbolExtractor;
use PhpSurface\Output\JsonRenderer;
use PhpSurface\Output\TextRenderer;
use PhpSurface\Version;

final class Application
{
    public function __construct(
        private readonly SymbolExtractor $symbolExtractor = new SymbolExtractor(),
        private readonly JsonRenderer $jsonRenderer = new JsonRenderer(),
        private readonly TextRenderer $textRenderer = new TextRenderer(),
    ) {
    }
    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        $args = array_slice($argv, 1);

        if ($args === []) {
            $this->printHelp();
            return ExitCode::USAGE;
        }

        if ($this->hasFlag($args, '--help', '-h')) {
            $this->printHelp();
            return ExitCode::SUCCESS;
        }

        if ($this->hasFlag($args, '--version', '-V')) {
            fwrite(STDOUT, Version::NAME . ' ' . Version::VERSION . PHP_EOL);
            return ExitCode::SUCCESS;
        }

        $file = $this->resolveFileArgument($args);
        if ($file === null) {
            fwrite(STDERR, 'Error: missing required argument <file.php>' . PHP_EOL);
            $this->printHelp();
            return ExitCode::USAGE;
        }

        $error = $this->validateFile($file);
        if ($error !== null) {
            fwrite(STDERR, 'Error: ' . $error . PHP_EOL);
            return ExitCode::FILE_ERROR;
        }

        try {
            $symbols = $this->symbolExtractor->extract($file);
        } catch (\Throwable $exception) {
            fwrite(STDERR, 'Error: failed to parse file: ' . $exception->getMessage() . PHP_EOL);
            return ExitCode::FILE_ERROR;
        }

        if ($this->hasFlag($args, '--text')) {
            fwrite(STDOUT, $this->textRenderer->render($file, $symbols));
        } else {
            fwrite(STDOUT, $this->jsonRenderer->render($file, $symbols));
        }

        return ExitCode::SUCCESS;
    }

    /**
     * @param list<string> $args
     */
    private function hasFlag(array $args, string ...$flags): bool
    {
        foreach ($flags as $flag) {
            if (in_array($flag, $args, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $args
     */
    private function resolveFileArgument(array $args): ?string
    {
        $skipNext = false;

        foreach ($args as $index => $arg) {
            if ($skipNext) {
                $skipNext = false;
                continue;
            }

            if ($arg === '--filter' || $arg === '--visibility' || $arg === '--show') {
                $skipNext = true;
                continue;
            }

            if (str_starts_with($arg, '-')) {
                continue;
            }

            return $arg;
        }

        return null;
    }

    private function validateFile(string $file): ?string
    {
        if (!str_ends_with(strtolower($file), '.php')) {
            return sprintf('"%s" is not a PHP file (.php extension required)', $file);
        }

        if (!is_file($file)) {
            return sprintf('file not found or not readable: %s', $file);
        }

        if (!is_readable($file)) {
            return sprintf('file not found or not readable: %s', $file);
        }

        return null;
    }

    private function printHelp(): void
    {
        $help = <<<'HELP'
Usage:
  php-surface <file.php> [options]

Structural explorer for PHP source files. Outputs a compact map of classes,
traits and interfaces so you can explore code incrementally.

Arguments:
  file.php              PHP source file to analyze

Options:
  --text                Human-readable output instead of JSON
  --filter <name>       Show only methods whose name matches
  --visibility <level>  Filter by public, protected or private
  --show <symbol>       Extract method body (e.g. save or ClassName::save)
  --full                Include structured parameters and full docblocks
  -h, --help            Show this help message
  -V, --version         Show version information

Examples:
  php-surface Foo.php
  php-surface Foo.php --text
  php-surface Foo.php --filter save
  php-surface Foo.php --visibility public
  php-surface Foo.php --show UserRepository::save
  php-surface Foo.php --full

HELP;

        fwrite(STDOUT, $help);
    }
}
