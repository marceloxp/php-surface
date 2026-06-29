<?php

declare(strict_types=1);

namespace Tests\Support;

final readonly class PhpSurfaceResult
{
    public function __construct(
        public int $exitCode,
        public string $stdout,
        public string $stderr,
    ) {
    }

    public function json(): array
    {
        return json_decode($this->stdout, true, 512, JSON_THROW_ON_ERROR);
    }
}
