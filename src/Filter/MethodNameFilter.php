<?php

declare(strict_types=1);

namespace PhpSurface\Filter;

final class MethodNameFilter
{
    /**
     * @param list<array<string, mixed>> $symbols
     *
     * @return list<array<string, mixed>>
     */
    public function apply(array $symbols, string $term): array
    {
        $filtered = [];

        foreach ($symbols as $symbol) {
            $filteredSymbol = $symbol;

            if (isset($symbol['methods']) && is_array($symbol['methods'])) {
                $filteredSymbol['methods'] = array_values(array_filter(
                    $symbol['methods'],
                    static fn (array $method): bool => str_contains($method['name'], $term)
                ));
            }

            $filtered[] = $filteredSymbol;
        }

        return $filtered;
    }
}
