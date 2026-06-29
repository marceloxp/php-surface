<?php

declare(strict_types=1);

namespace PhpSurface\Cli;

use PhpSurface\Extractor\ShowExtractor;
use PhpSurface\Extractor\SymbolExtractor;
use PhpSurface\Filter\MethodNameFilter;
use PhpSurface\Filter\VisibilityFilter;
use PhpSurface\Output\JsonRenderer;
use PhpSurface\Output\ShowRenderer;
use PhpSurface\Output\TextRenderer;
use PhpSurface\Version;

final class Application
{
    public function __construct(
        private readonly SymbolExtractor $symbolExtractor = new SymbolExtractor(),
        private readonly MethodNameFilter $methodNameFilter = new MethodNameFilter(),
        private readonly VisibilityFilter $visibilityFilter = new VisibilityFilter(),
        private readonly ShowExtractor $showExtractor = new ShowExtractor(),
        private readonly JsonRenderer $jsonRenderer = new JsonRenderer(),
        private readonly TextRenderer $textRenderer = new TextRenderer(),
        private readonly ShowRenderer $showRenderer = new ShowRenderer(),
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

        $filter = $this->resolveOptionValue($args, '--filter');
        if ($this->hasFlag($args, '--filter') && ($filter === null || $filter === '')) {
            fwrite(STDERR, 'Error: --filter requires a value' . PHP_EOL);
            return ExitCode::USAGE;
        }

        $visibility = $this->resolveOptionValue($args, '--visibility');
        if ($this->hasFlag($args, '--visibility') && ($visibility === null || $visibility === '')) {
            fwrite(STDERR, 'Error: --visibility requires a value' . PHP_EOL);
            return ExitCode::USAGE;
        }

        if ($visibility !== null && $visibility !== '' && !in_array($visibility, VisibilityFilter::LEVELS, true)) {
            fwrite(
                STDERR,
                'Error: --visibility must be one of: ' . implode(', ', VisibilityFilter::LEVELS) . PHP_EOL
            );
            return ExitCode::USAGE;
        }

        $show = $this->resolveOptionValue($args, '--show');
        if ($this->hasFlag($args, '--show') && ($show === null || $show === '')) {
            fwrite(STDERR, 'Error: --show requires a value' . PHP_EOL);
            return ExitCode::USAGE;
        }

        try {
            $symbols = $this->symbolExtractor->extract($file, $this->hasFlag($args, '--full'));
        } catch (\Throwable $exception) {
            fwrite(STDERR, 'Error: failed to parse file: ' . $exception->getMessage() . PHP_EOL);
            return ExitCode::FILE_ERROR;
        }

        if ($show !== null && $show !== '') {
            $matches = $this->showExtractor->extract($file, $symbols, $show);
            if ($matches === []) {
                fwrite(STDERR, sprintf('Error: no symbol matched "%s"' . PHP_EOL, $show));
                return ExitCode::USAGE;
            }

            if ($this->hasFlag($args, '--text')) {
                fwrite(STDOUT, $this->showRenderer->renderText($file, $matches));
            } else {
                fwrite(STDOUT, $this->showRenderer->renderJson($file, $matches));
            }

            return ExitCode::SUCCESS;
        }

        if ($visibility !== null && $visibility !== '') {
            $symbols = $this->visibilityFilter->apply($symbols, $visibility);
        }

        if ($filter !== null && $filter !== '') {
            $symbols = $this->methodNameFilter->apply($symbols, $filter);
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

    /**
     * @param list<string> $args
     */
    private function resolveOptionValue(array $args, string $option): ?string
    {
        foreach ($args as $index => $arg) {
            if ($arg === $option) {
                return $args[$index + 1] ?? null;
            }

            $prefix = $option . '=';
            if (str_starts_with($arg, $prefix)) {
                return substr($arg, strlen($prefix));
            }
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
  --filter <name>       Show only methods whose name contains <name> (case-sensitive)
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
