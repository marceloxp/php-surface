<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

final class ShowExtractor
{
    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return list<array<string, mixed>>
     */
    public function extract(string $filePath, array $symbols, string $query): array
    {
        [$symbolQuery, $methodName] = $this->parseQuery($query);
        $sourceLines = file($filePath);
        if ($sourceLines === false) {
            throw new \RuntimeException(sprintf('Unable to read file: %s', $filePath));
        }

        $matches = [];

        foreach ($symbols as $symbol) {
            if ($symbolQuery !== null && !$this->symbolMatches($symbol, $symbolQuery)) {
                continue;
            }

            foreach ($symbol['methods'] ?? [] as $method) {
                if (!is_array($method) || ($method['name'] ?? '') !== $methodName) {
                    continue;
                }

                $startLine = (int) ($method['startLine'] ?? 0);
                $endLine = (int) ($method['endLine'] ?? $startLine);

                $matches[] = [
                    'symbol' => $symbol['name'],
                    'namespace' => $symbol['namespace'] ?? '',
                    'type' => $symbol['type'],
                    'method' => $method['name'],
                    'startLine' => $startLine,
                    'endLine' => $endLine,
                    'source' => $this->readSourceRange($sourceLines, $startLine, $endLine),
                ];
            }
        }

        return $matches;
    }

    /**
     * @return array{0: string|null, 1: string}
     */
    private function parseQuery(string $query): array
    {
        if (str_contains($query, '::')) {
            [$symbolQuery, $methodName] = explode('::', $query, 2);

            return [trim($symbolQuery), trim($methodName)];
        }

        return [null, trim($query)];
    }

    /**
     * @param array<string, mixed> $symbol
     */
    private function symbolMatches(array $symbol, string $symbolQuery): bool
    {
        $name = (string) ($symbol['name'] ?? '');
        $namespace = (string) ($symbol['namespace'] ?? '');
        $fqn = $namespace !== '' ? $namespace . '\\' . $name : $name;

        return $name === $symbolQuery
            || $fqn === $symbolQuery
            || str_ends_with($fqn, '\\' . $symbolQuery);
    }

    /**
     * @param list<string> $sourceLines
     */
    private function readSourceRange(array $sourceLines, int $startLine, int $endLine): string
    {
        if ($startLine < 1 || $endLine < $startLine) {
            return '';
        }

        $slice = array_slice($sourceLines, $startLine - 1, $endLine - $startLine + 1);

        return implode('', $slice);
    }
}
