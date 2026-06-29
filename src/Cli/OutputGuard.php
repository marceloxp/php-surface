<?php

declare(strict_types=1);

namespace PhpSurface\Cli;

final class OutputGuard
{
    public const DEFAULT_LIMIT_BYTES = 8192;

    public const ERROR_CODE = 'output_too_large';

    public function getLimitBytes(): int
    {
        $value = getenv('PHP_SURFACE_MAX_OUTPUT_BYTES');

        if ($value === false || $value === '') {
            return self::DEFAULT_LIMIT_BYTES;
        }

        $limit = (int) $value;

        if ($limit <= 0) {
            return self::DEFAULT_LIMIT_BYTES;
        }

        return $limit;
    }

    public function isAllowed(int $outputBytes, bool $allowLargeOutput): bool
    {
        if ($allowLargeOutput) {
            return true;
        }

        return $outputBytes <= $this->getLimitBytes();
    }

    /**
     * @param list<string> $hints
     */
    public function formatBlockedJson(
        string $file,
        int $fileBytes,
        int $outputBytes,
        int $limitBytes,
        array $hints,
    ): string {
        $payload = [
            'error' => self::ERROR_CODE,
            'file' => $file,
            'fileBytes' => $fileBytes,
            'outputBytes' => $outputBytes,
            'limitBytes' => $limitBytes,
            'hints' => $hints,
        ];

        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . PHP_EOL;
    }

    /**
     * @param list<string> $hints
     */
    public function formatBlockedText(
        string $file,
        int $fileBytes,
        int $outputBytes,
        int $limitBytes,
        array $hints,
    ): string {
        $lines = [
            sprintf(
                'Error: output too large (%d bytes, limit %d).',
                $outputBytes,
                $limitBytes,
            ),
            sprintf('File: %s (%d bytes)', $file, $fileBytes),
            '',
            'Narrow the exploration instead of dumping the full output:',
            '',
        ];

        foreach ($hints as $hint) {
            $lines[] = '  ' . $hint;
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
