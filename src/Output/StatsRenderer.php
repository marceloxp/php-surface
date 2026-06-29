<?php

declare(strict_types=1);

namespace PhpSurface\Output;

final class StatsRenderer
{
    /**
     * @param array<string, mixed> $stats
     */
    public function renderJson(array $stats): string
    {
        return json_encode(
            $stats,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $stats
     */
    public function renderText(array $stats): string
    {
        $lines = [
            $stats['file'],
            sprintf('%d bytes, %d lines', $stats['bytes'], $stats['lines']),
            '',
            $this->formatSymbolLine($stats['symbols']),
            $this->formatMethodLine($stats['methods']),
            sprintf('Docblocks: %d methods with summary', $stats['methods']['withDocblock']),
            '',
            'Largest by methods:',
        ];

        foreach ($stats['largest'] as $entry) {
            $lines[] = sprintf(
                '  %s %s — %d methods (line %d)',
                $entry['type'],
                $this->formatQualifiedName($entry),
                $entry['methods'],
                $entry['line'],
            );
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $symbols
     */
    private function formatSymbolLine(array $symbols): string
    {
        $byType = $symbols['byType'];

        return sprintf(
            'Symbols: %d (%d classes, %d interfaces, %d traits)',
            $symbols['total'],
            $byType['class'],
            $byType['interface'],
            $byType['trait'],
        );
    }

    /**
     * @param array<string, mixed> $methods
     */
    private function formatMethodLine(array $methods): string
    {
        $byVisibility = $methods['byVisibility'];

        return sprintf(
            'Methods: %d (%d public, %d protected, %d private)',
            $methods['total'],
            $byVisibility['public'],
            $byVisibility['protected'],
            $byVisibility['private'],
        );
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function formatQualifiedName(array $entry): string
    {
        $namespace = $entry['namespace'] ?? '';

        if (is_string($namespace) && $namespace !== '') {
            return $namespace . '\\' . $entry['name'];
        }

        return (string) $entry['name'];
    }
}
