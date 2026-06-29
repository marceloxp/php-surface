<?php

declare(strict_types=1);

namespace PhpSurface\Filter;

final class VisibilityFilter
{
    public const LEVELS = ['public', 'protected', 'private'];

    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return list<array<string, mixed>>
     */
    public function apply(array $symbols, string $visibility): array
    {
        $filtered = [];

        foreach ($symbols as $symbol) {
            $filteredSymbol = $symbol;

            if (isset($symbol['methods']) && is_array($symbol['methods'])) {
                $filteredSymbol['methods'] = array_values(array_filter(
                    $symbol['methods'],
                    static fn (array $method): bool => ($method['visibility'] ?? '') === $visibility
                ));
            }

            $filtered[] = $filteredSymbol;
        }

        return $filtered;
    }
}
