<?php

declare(strict_types=1);

namespace PhpSurface\Analyzer;

final class StatsCollector
{
    private const int LARGEST_LIMIT = 10;

    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return array<string, mixed>
     */
    public function collect(string $file, array $symbols): array
    {
        $byType = [
            'class' => 0,
            'interface' => 0,
            'trait' => 0,
        ];

        $byVisibility = [
            'public' => 0,
            'protected' => 0,
            'private' => 0,
        ];

        $methodTotal = 0;
        $withDocblock = 0;
        $largest = [];

        foreach ($symbols as $symbol) {
            $type = $symbol['type'];
            if (isset($byType[$type])) {
                $byType[$type]++;
            }

            $methods = $symbol['methods'] ?? [];
            $methodCount = count($methods);
            $methodTotal += $methodCount;

            if ($methodCount > 0) {
                $largest[] = [
                    'name' => $symbol['name'],
                    'namespace' => $symbol['namespace'] ?? '',
                    'type' => $type,
                    'methods' => $methodCount,
                    'line' => $symbol['line'] ?? 0,
                ];
            }

            foreach ($methods as $method) {
                $visibility = $method['visibility'] ?? '';
                if (isset($byVisibility[$visibility])) {
                    $byVisibility[$visibility]++;
                }

                if (isset($method['docblock'])) {
                    $withDocblock++;
                }
            }
        }

        usort(
            $largest,
            static fn (array $left, array $right): int => $right['methods'] <=> $left['methods']
                ?: $left['name'] <=> $right['name']
        );

        if (count($largest) > self::LARGEST_LIMIT) {
            $largest = array_slice($largest, 0, self::LARGEST_LIMIT);
        }

        return [
            'file' => $file,
            'bytes' => filesize($file),
            'lines' => $this->countLines($file),
            'symbols' => [
                'total' => count($symbols),
                'byType' => $byType,
            ],
            'methods' => [
                'total' => $methodTotal,
                'byVisibility' => $byVisibility,
                'withDocblock' => $withDocblock,
            ],
            'largest' => $largest,
        ];
    }

    private function countLines(string $file): int
    {
        $handle = new \SplFileObject($file, 'r');
        $handle->seek(PHP_INT_MAX);

        return $handle->key() + 1;
    }
}
