<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

use voku\SimplePhpParser\Model\BasePHPElement;
use voku\SimplePhpParser\Parsers\PhpCodeParser;

final class SymbolExtractor
{
    /**
     * @return list<array{
     *     name: string,
     *     namespace: string,
     *     type: string,
     *     line: int
     * }>
     */
    public function extract(string $filePath): array
    {
        $container = PhpCodeParser::getPhpFiles($filePath);
        $symbols = [];

        foreach ($container->getInterfaces() as $interface) {
            $symbols[] = $this->mapSymbol($interface, 'interface');
        }

        foreach ($container->getTraits() as $trait) {
            $symbols[] = $this->mapSymbol($trait, 'trait');
        }

        foreach ($container->getClasses() as $class) {
            if ($class->is_anonymous) {
                continue;
            }

            $symbols[] = $this->mapSymbol($class, 'class');
        }

        usort(
            $symbols,
            static fn (array $left, array $right): int => $left['line'] <=> $right['line']
                ?: $left['name'] <=> $right['name']
        );

        return $symbols;
    }

    /**
     * @return array{name: string, namespace: string, type: string, line: int}
     */
    private function mapSymbol(BasePHPElement $element, string $type): array
    {
        [$namespace, $name] = $this->splitFqn($element->name);

        return [
            'name' => $name,
            'namespace' => $namespace,
            'type' => $type,
            'line' => $element->line ?? 0,
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitFqn(string $fqn): array
    {
        $fqn = ltrim($fqn, '\\');
        $separator = strrpos($fqn, '\\');

        if ($separator === false) {
            return ['', $fqn];
        }

        return [
            substr($fqn, 0, $separator),
            substr($fqn, $separator + 1),
        ];
    }
}
