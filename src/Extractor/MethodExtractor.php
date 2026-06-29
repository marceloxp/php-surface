<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

use PhpSurface\Parser\MethodAstIndex;
use voku\SimplePhpParser\Model\BasePHPClass;
use voku\SimplePhpParser\Model\PHPMethod;

final class MethodExtractor
{
    public function __construct(
        private readonly MethodAstIndex $methodAstIndex = new MethodAstIndex(),
        private readonly DocblockExtractor $docblockExtractor = new DocblockExtractor(),
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function extract(BasePHPClass $symbol, string $filePath): array
    {
        $astIndex = $this->methodAstIndex->index($filePath);
        $sourceLines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($sourceLines === false) {
            throw new \RuntimeException(sprintf('Unable to read file: %s', $filePath));
        }

        $methods = [];

        foreach ($symbol->methods as $method) {
            $methods[] = $this->mapMethod($method, $sourceLines, $astIndex);
        }

        usort(
            $methods,
            static fn (array $left, array $right): int => $left['startLine'] <=> $right['startLine']
                ?: $left['name'] <=> $right['name']
        );

        return $methods;
    }

    /**
     * @param list<string> $sourceLines
     * @param array<int, array{endLine: int, isAbstract: bool, docComment: string|null}> $astIndex
     *
     * @return array<string, mixed>
     */
    private function mapMethod(PHPMethod $method, array $sourceLines, array $astIndex): array
    {
        $startLine = $method->line ?? 0;
        $methodMeta = $astIndex[$startLine] ?? null;
        $endLine = $methodMeta['endLine'] ?? $startLine;
        $isAbstract = $methodMeta['isAbstract'] ?? false;

        $mapped = [
            'name' => $method->name,
            'visibility' => $method->access,
            'signature' => $this->extractSignature($sourceLines, $startLine, $endLine),
            'startLine' => $startLine,
            'endLine' => $endLine,
        ];

        if ($method->returnType !== null && $method->returnType !== '') {
            $mapped['returnType'] = $method->returnType;
        }

        $modifiers = $this->collectModifiers($method, $isAbstract);
        if ($modifiers !== []) {
            $mapped['modifiers'] = $modifiers;
        }

        $docComment = $methodMeta !== null ? $methodMeta['docComment'] : null;
        $docblock = $this->docblockExtractor->extract($method, $docComment);
        if ($docblock !== null) {
            $mapped['docblock'] = $docblock;
        }

        return $mapped;
    }

    /**
     * @return list<string>
     */
    private function collectModifiers(PHPMethod $method, bool $isAbstract): array
    {
        $modifiers = [];

        if ($isAbstract) {
            $modifiers[] = 'abstract';
        }

        if ($method->is_final === true) {
            $modifiers[] = 'final';
        }

        if ($method->is_static === true) {
            $modifiers[] = 'static';
        }

        return $modifiers;
    }

    /**
     * @param list<string> $sourceLines
     */
    private function extractSignature(array $sourceLines, int $startLine, int $endLine): string
    {
        $parts = [];

        for ($line = $startLine; $line <= $endLine; $line++) {
            $parts[] = rtrim($sourceLines[$line - 1] ?? '');
        }

        $text = preg_replace('/\s+/', ' ', implode(' ', $parts)) ?? '';

        $bracePosition = strpos($text, '{');
        if ($bracePosition !== false) {
            $text = substr($text, 0, $bracePosition);
        }

        return rtrim(trim($text), ';');
    }
}
