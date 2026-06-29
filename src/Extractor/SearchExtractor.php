<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

final class SearchExtractor
{
    private const int SOURCE_LINE_LIMIT = 30;

    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return list<array<string, mixed>>
     */
    public function search(string $file, array $symbols, string $query): array
    {
        $needle = strtolower($query);
        $matches = [];

        foreach ($symbols as $symbol) {
            $name = (string) ($symbol['name'] ?? '');
            $namespace = (string) ($symbol['namespace'] ?? '');
            $fqn = $namespace !== '' ? $namespace . '\\' . $name : $name;

            if (
                str_contains(strtolower($name), $needle)
                || str_contains(strtolower($namespace), $needle)
                || str_contains(strtolower($fqn), $needle)
            ) {
                $matches[] = $this->symbolMatch($file, $symbol);
            }

            foreach ($symbol['methods'] ?? [] as $method) {
                if (!is_array($method)) {
                    continue;
                }

                $methodName = (string) ($method['name'] ?? '');
                if ($methodName === '' || !str_contains(strtolower($methodName), $needle)) {
                    continue;
                }

                $matches[] = $this->methodMatch($file, $symbol, $method);
            }
        }

        foreach ($this->searchSourceLines($file, $needle) as $sourceMatch) {
            $matches[] = $sourceMatch;
        }

        usort(
            $matches,
            static fn (array $left, array $right): int => ($left['line'] ?? 0) <=> ($right['line'] ?? 0)
                ?: ($left['kind'] ?? '') <=> ($right['kind'] ?? '')
        );

        return $matches;
    }

    /**
     * @param array<string, mixed> $symbol
     *
     * @return array<string, mixed>
     */
    private function symbolMatch(string $file, array $symbol): array
    {
        $name = (string) ($symbol['name'] ?? '');
        $namespace = (string) ($symbol['namespace'] ?? '');
        $methods = $symbol['methods'] ?? [];
        $firstMethod = is_array($methods) && isset($methods[0]) && is_array($methods[0]) ? $methods[0] : null;

        $match = [
            'kind' => 'symbol',
            'name' => $name,
            'namespace' => $namespace,
            'type' => $symbol['type'] ?? '',
            'line' => $symbol['line'] ?? 0,
        ];

        $hint = $this->buildShowHint($file, $name, $firstMethod);
        if ($hint !== null) {
            $match['hint'] = $hint;
        }

        return $match;
    }

    /**
     * @param array<string, mixed> $symbol
     * @param array<string, mixed> $method
     *
     * @return array<string, mixed>
     */
    private function methodMatch(string $file, array $symbol, array $method): array
    {
        $name = (string) ($symbol['name'] ?? '');
        $namespace = (string) ($symbol['namespace'] ?? '');
        $methodName = (string) ($method['name'] ?? '');

        $match = [
            'kind' => 'method',
            'symbol' => $name,
            'namespace' => $namespace,
            'type' => $symbol['type'] ?? '',
            'method' => $methodName,
            'line' => $method['startLine'] ?? $symbol['line'] ?? 0,
        ];

        $hint = $this->buildShowHint($file, $name, $method);
        if ($hint !== null) {
            $match['hint'] = $hint;
        }

        return $match;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchSourceLines(string $file, string $needle): array
    {
        $lines = file($file);
        if ($lines === false) {
            return [];
        }

        $matches = [];

        foreach ($lines as $index => $line) {
            if (!str_contains(strtolower($line), $needle)) {
                continue;
            }

            $matches[] = [
                'kind' => 'source',
                'line' => $index + 1,
                'text' => rtrim($line),
            ];

            if (count($matches) >= self::SOURCE_LINE_LIMIT) {
                break;
            }
        }

        return $matches;
    }

    /**
     * @param array<string, mixed>|null $method
     */
    private function buildShowHint(string $file, string $className, ?array $method): ?string
    {
        if ($className === '' || $method === null) {
            return null;
        }

        $methodName = (string) ($method['name'] ?? '');
        if ($methodName === '') {
            return null;
        }

        return sprintf('php-surface %s --show %s::%s', $file, $className, $methodName);
    }
}
