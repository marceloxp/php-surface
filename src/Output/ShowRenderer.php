<?php

declare(strict_types=1);

namespace PhpSurface\Output;

final class ShowRenderer
{
    /**
     * @param list<array<string, mixed>> $matches
     */
    public function renderJson(string $file, array $matches): string
    {
        $payload = [
            'file' => $file,
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
    public function renderText(string $file, array $matches): string
    {
        $lines = [$file, ''];

        foreach ($matches as $match) {
            $namespace = (string) ($match['namespace'] ?? '');
            $symbol = (string) ($match['symbol'] ?? '');
            $fqn = $namespace !== '' ? $namespace . '\\' . $symbol : $symbol;

            $lines[] = sprintf(
                '%s::%s (lines %d-%d)',
                $fqn,
                $match['method'] ?? '',
                $match['startLine'] ?? 0,
                $match['endLine'] ?? 0
            );
            $lines[] = rtrim((string) ($match['source'] ?? ''));
            $lines[] = '';
        }

        while ($lines !== [] && end($lines) === '') {
            array_pop($lines);
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
