<?php

declare(strict_types=1);

namespace PhpSurface\Output;

final class SearchRenderer
{
    /**
     * @param list<array<string, mixed>> $matches
     */
    public function renderJson(string $file, string $query, array $matches): string
    {
        $payload = [
            'file' => $file,
            'query' => $query,
            'matches' => $matches,
        ];

        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . PHP_EOL;
    }

    /**
     * @param list<array<string, mixed>> $matches
     */
    public function renderText(string $file, string $query, array $matches): string
    {
        $lines = [
            $file,
            'query: ' . $query,
            '',
        ];

        foreach ($matches as $match) {
            $lines[] = $this->formatMatchLine($match);

            if (isset($match['hint']) && is_string($match['hint'])) {
                $lines[] = '  ' . $match['hint'];
            }

            if (isset($match['text']) && is_string($match['text'])) {
                $lines[] = '  ' . $match['text'];
            }

            $lines[] = '';
        }

        while ($lines !== [] && end($lines) === '') {
            array_pop($lines);
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    /**
     * @param array<string, mixed> $match
     */
    private function formatMatchLine(array $match): string
    {
        $kind = (string) ($match['kind'] ?? 'match');

        if ($kind === 'symbol') {
            $namespace = (string) ($match['namespace'] ?? '');
            $name = (string) ($match['name'] ?? '');
            $fqn = $namespace !== '' ? $namespace . '\\' . $name : $name;

            return sprintf(
                '%s  %s %s (line %d)',
                $kind,
                $match['type'] ?? '',
                $fqn,
                $match['line'] ?? 0,
            );
        }

        if ($kind === 'method') {
            $namespace = (string) ($match['namespace'] ?? '');
            $symbol = (string) ($match['symbol'] ?? '');
            $fqn = $namespace !== '' ? $namespace . '\\' . $symbol : $symbol;

            return sprintf(
                '%s  %s::%s (line %d)',
                $kind,
                $fqn,
                $match['method'] ?? '',
                $match['line'] ?? 0,
            );
        }

        return sprintf('%s  line %d', $kind, $match['line'] ?? 0);
    }
}
