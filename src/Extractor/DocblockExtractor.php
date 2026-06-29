<?php

declare(strict_types=1);

namespace PhpSurface\Extractor;

use voku\SimplePhpParser\Model\PHPMethod;
use voku\SimplePhpParser\Parsers\Helper\DocFactoryProvider;

final class DocblockExtractor
{
    /**
     * @return array{summary?: string, text?: string, return?: string, throws?: list<string>}|null
     */
    public function extract(PHPMethod $method, ?string $docCommentText, bool $full = false): ?array
    {
        if ($docCommentText === null) {
            return null;
        }

        $docblock = [];

        if ($full) {
            $text = trim($docCommentText);
            if ($text !== '') {
                $docblock['text'] = $text;
            }
        } else {
            $summary = trim($method->summary);
            if ($summary !== '') {
                $docblock['summary'] = $summary;
            }
        }

        if ($method->returnPhpDocRaw !== null) {
            $return = trim($this->normalizeDocText($method->returnPhpDocRaw));
            if ($return !== '') {
                $docblock['return'] = $return;
            }
        }

        $throws = $this->extractThrows($docCommentText);
        if ($throws !== []) {
            $docblock['throws'] = $throws;
        }

        if ($docblock === []) {
            return null;
        }

        return $docblock;
    }

    /**
     * @return list<string>
     */
    private function extractThrows(string $docCommentText): array
    {
        try {
            $phpDoc = DocFactoryProvider::getDocFactory()->create($docCommentText);
        } catch (\Exception) {
            return [];
        }

        $throws = [];

        foreach ($phpDoc->getTagsByName('throws') as $tag) {
            $text = trim($this->normalizeDocText((string) $tag));
            if ($text !== '') {
                $throws[] = $text;
            }
        }

        return $throws;
    }

    private function normalizeDocText(string $text): string
    {
        return preg_replace('/\\\\([A-Za-z_])/', '$1', $text) ?? $text;
    }
}
