<?php

declare(strict_types=1);

namespace PhpSurface\Output;

final class TextRenderer
{
    /**
     * @param list<array<string, mixed>> $symbols
     */
    public function render(string $file, array $symbols): string
    {
        $lines = [$file, ''];

        foreach ($symbols as $symbol) {
            $lines[] = $this->formatSymbolHeader($symbol);

            foreach ($symbol['methods'] ?? [] as $method) {
                $lines[] = '  ' . $method['signature'];

                $summary = $method['docblock']['text'] ?? $method['docblock']['summary'] ?? null;
                if (is_string($summary) && $summary !== '') {
                    $lines[] = '      ' . $summary;
                }
            }

            $lines[] = '';
        }

        while ($lines !== [] && end($lines) === '') {
            array_pop($lines);
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $symbol
     */
    private function formatSymbolHeader(array $symbol): string
    {
        $name = $symbol['name'];
        $namespace = $symbol['namespace'] ?? '';

        if (is_string($namespace) && $namespace !== '') {
            $name = $namespace . '\\' . $name;
        }

        return sprintf('%s %s (line %d)', $symbol['type'], $name, $symbol['line']);
    }
}
