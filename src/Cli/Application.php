<?php

declare(strict_types=1);

namespace PhpSurface\Cli;

use PhpSurface\Analyzer\StatsCollector;
use PhpSurface\Parser\ParseException;
use PhpSurface\Extractor\SearchExtractor;
use PhpSurface\Extractor\ShowExtractor;
use PhpSurface\Extractor\SymbolExtractor;
use PhpSurface\Filter\MethodNameFilter;
use PhpSurface\Filter\VisibilityFilter;
use PhpSurface\Output\JsonRenderer;
use PhpSurface\Output\SearchRenderer;
use PhpSurface\Output\ShowRenderer;
use PhpSurface\Output\StatsRenderer;
use PhpSurface\Output\TextRenderer;
use PhpSurface\Version;

final class Application
{
    public function __construct(
        private readonly SymbolExtractor $symbolExtractor = new SymbolExtractor(),
        private readonly MethodNameFilter $methodNameFilter = new MethodNameFilter(),
        private readonly VisibilityFilter $visibilityFilter = new VisibilityFilter(),
        private readonly ShowExtractor $showExtractor = new ShowExtractor(),
        private readonly SearchExtractor $searchExtractor = new SearchExtractor(),
        private readonly JsonRenderer $jsonRenderer = new JsonRenderer(),
        private readonly TextRenderer $textRenderer = new TextRenderer(),
        private readonly ShowRenderer $showRenderer = new ShowRenderer(),
        private readonly SearchRenderer $searchRenderer = new SearchRenderer(),
        private readonly StatsCollector $statsCollector = new StatsCollector(),
        private readonly StatsRenderer $statsRenderer = new StatsRenderer(),
        private readonly OutputGuard $outputGuard = new OutputGuard(),
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

        $search = $this->resolveOptionValue($args, '--search');
        if ($this->hasFlag($args, '--search') && ($search === null || $search === '')) {
            fwrite(STDERR, 'Error: --search requires a value' . PHP_EOL);
            return ExitCode::USAGE;
        }

        try {
            $symbols = $this->symbolExtractor->extract($file, $this->hasFlag($args, '--full'));
        } catch (ParseException $exception) {
            fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
            return ExitCode::FILE_ERROR;
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

            $isText = $this->hasFlag($args, '--text');
            $output = $isText
                ? $this->showRenderer->renderText($file, $matches)
                : $this->showRenderer->renderJson($file, $matches);

            return $this->emitOutput($output, $file, $args, $isText, $symbols, $show);
        }

        if ($search !== null && $search !== '') {
            $searchSymbols = $symbols;

            if ($visibility !== null && $visibility !== '') {
                $searchSymbols = $this->visibilityFilter->apply($searchSymbols, $visibility);
            }

            $matches = $this->searchExtractor->search($file, $searchSymbols, $search);
            $isText = $this->hasFlag($args, '--text');
            $output = $isText
                ? $this->searchRenderer->renderText($file, $search, $matches)
                : $this->searchRenderer->renderJson($file, $search, $matches);

            return $this->emitOutput($output, $file, $args, $isText, $symbols);
        }

        if ($visibility !== null && $visibility !== '') {
            $symbols = $this->visibilityFilter->apply($symbols, $visibility);
        }

        if ($filter !== null && $filter !== '') {
            $symbols = $this->methodNameFilter->apply($symbols, $filter);
        }

        if ($this->hasFlag($args, '--stats')) {
            $stats = $this->statsCollector->collect($file, $symbols);
            $isText = $this->hasFlag($args, '--text');
            $output = $isText
                ? $this->statsRenderer->renderText($stats)
                : $this->statsRenderer->renderJson($stats);

            return $this->emitOutput($output, $file, $args, $isText, $symbols);
        }

        $isText = $this->hasFlag($args, '--text');
        $output = $isText
            ? $this->textRenderer->render($file, $symbols)
            : $this->jsonRenderer->render($file, $symbols);

        return $this->emitOutput($output, $file, $args, $isText, $symbols);
    }

    /**
     * @param list<string> $args
     * @param list<array<string, mixed>> $symbols
     */
    private function emitOutput(
        string $output,
        string $file,
        array $args,
        bool $isText,
        array $symbols = [],
        ?string $showTarget = null,
    ): int {
        $outputBytes = strlen($output);

        if (!$this->outputGuard->isAllowed($outputBytes, $this->hasFlag($args, '--allow-large-output'))) {
            $limitBytes = $this->outputGuard->getLimitBytes();
            $hints = $this->buildHints($file, $showTarget, $symbols);
            $fileBytes = filesize($file);

            $blocked = $isText
                ? $this->outputGuard->formatBlockedText($file, $fileBytes, $outputBytes, $limitBytes, $hints)
                : $this->outputGuard->formatBlockedJson($file, $fileBytes, $outputBytes, $limitBytes, $hints);

            fwrite(STDERR, $blocked);

            return ExitCode::OUTPUT_TOO_LARGE;
        }

        fwrite(STDOUT, $output);

        return ExitCode::SUCCESS;
    }

    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return list<string>
     */
    private function buildHints(string $file, ?string $showTarget, array $symbols): array
    {
        if ($showTarget !== null && $showTarget !== '') {
            return [
                sprintf('php-surface %s --allow-large-output --show %s', $file, $showTarget),
            ];
        }

        $hints = [
            sprintf('php-surface %s --stats', $file),
            sprintf('php-surface %s --search <term>', $file),
            sprintf('php-surface %s --visibility public', $file),
        ];

        $example = $this->exampleExplorationTarget($symbols);
        if ($example !== null) {
            $hints[] = sprintf('php-surface %s --filter %s', $file, $example['method']);
            $hints[] = sprintf('php-surface %s --show %s', $file, $example['show']);
        } else {
            $hints[] = sprintf('php-surface %s --filter <method>', $file);
            $hints[] = sprintf('php-surface %s --show ClassName::method', $file);
        }

        $hints[] = sprintf('php-surface %s --allow-large-output', $file);

        return $hints;
    }

    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return array{method: string, show: string}|null
     */
    private function exampleExplorationTarget(array $symbols): ?array
    {
        $best = null;
        $bestCount = 0;

        foreach ($symbols as $symbol) {
            $methods = $symbol['methods'] ?? [];
            $count = count($methods);

            if ($count > $bestCount) {
                $bestCount = $count;
                $best = $symbol;
            }
        }

        if ($best === null || $bestCount === 0) {
            return null;
        }

        $method = $best['methods'][0]['name'] ?? null;
        $className = $best['name'] ?? null;

        if (!is_string($method) || !is_string($className)) {
            return null;
        }

        return [
            'method' => $method,
            'show' => $className . '::' . $method,
        ];
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

            if ($arg === '--filter' || $arg === '--visibility' || $arg === '--show' || $arg === '--search') {
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
  --search <term>       Find symbols, methods and source lines (case-insensitive)
  --visibility <level>  Filter by public, protected or private
  --show <symbol>       Extract method body (e.g. save or ClassName::save)
  --stats               Summary counts instead of full symbol map
  --full                Include structured parameters and full docblocks
  --allow-large-output  Skip output size guard (default limit: 8 KB)
  -h, --help            Show this help message
  -V, --version         Show version information

Environment:
  PHP_SURFACE_MAX_OUTPUT_BYTES  Override default output limit (8192)

Examples:
  php-surface Foo.php
  php-surface Foo.php --text
  php-surface Foo.php --filter save
  php-surface Foo.php --search nested
  php-surface Foo.php --visibility public
  php-surface Foo.php --show UserRepository::save
  php-surface Foo.php --stats
  php-surface Foo.php --full
  php-surface Foo.php --allow-large-output

HELP;

        fwrite(STDOUT, $help);
    }
}
