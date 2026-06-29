<?php

declare(strict_types=1);

namespace PhpSurface\Output;

final class JsonRenderer
{
    /**
     * @param list<array<string, mixed>> $symbols
     */
    public function render(string $file, array $symbols): string
    {
        $payload = [
            'file' => $file,
            'symbols' => $symbols,
        ];

        return json_encode(
            $payload,
            JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . PHP_EOL;
    }
}
