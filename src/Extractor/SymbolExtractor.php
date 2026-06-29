<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

use PhpSurface\Parser\ParseException;
use voku\SimplePhpParser\Model\BasePHPClass;
use voku\SimplePhpParser\Model\BasePHPElement;
use voku\SimplePhpParser\Parsers\PhpCodeParser;

final class SymbolExtractor
{
    public function __construct(
        private readonly MethodExtractor $methodExtractor = new MethodExtractor(),
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function extract(string $filePath, bool $full = false): array
    {
        $container = PhpCodeParser::getPhpFiles($filePath);
        $parseErrors = $container->getParseErrors();

        if ($parseErrors !== []) {
            throw new ParseException($this->formatParseError($parseErrors[0]));
        }

        $symbols = [];

        foreach ($container->getInterfaces() as $interface) {
            $symbols[] = $this->mapSymbol($interface, 'interface', $filePath, $full);
        }

        foreach ($container->getTraits() as $trait) {
            $symbols[] = $this->mapSymbol($trait, 'trait', $filePath, $full);
        }

        foreach ($container->getClasses() as $class) {
            if ($class->is_anonymous) {
                continue;
            }

            $symbols[] = $this->mapSymbol($class, 'class', $filePath, $full);
        }

        usort(
            $symbols,
            static fn (array $left, array $right): int => $left['line'] <=> $right['line']
                ?: $left['name'] <=> $right['name']
        );

        return $symbols;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapSymbol(BasePHPElement $element, string $type, string $filePath, bool $full): array
    {
        [$namespace, $name] = $this->splitFqn($element->name);

        $symbol = [
            'name' => $name,
            'namespace' => $namespace,
            'type' => $type,
            'line' => $element->line ?? 0,
        ];

        if ($element instanceof BasePHPClass) {
            $methods = $this->methodExtractor->extract($element, $filePath, $full);
            if ($methods !== []) {
                $symbol['methods'] = $methods;
            }
        }

        return $symbol;
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

    private function formatParseError(string $error): string
    {
        $message = $error;

        if (preg_match('/\|\s*(.+)$/m', $error, $matches) === 1) {
            $message = $matches[1];
        }

        $message = trim(strtok($message, "\n") ?: $message);

        if (preg_match('/^Syntax error/i', $message) === 1) {
            return $message;
        }

        return $message !== '' ? $message : 'invalid PHP syntax';
    }
}
