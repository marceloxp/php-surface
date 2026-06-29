<?php

declare(strict_types=1);

function fixture(string $filename): string
{
    return 'tests/fixtures/' . ltrim($filename, '/');
}

function runPhpSurface(array $args, array $env = []): Tests\Support\PhpSurfaceResult
{
    return (new Tests\Support\PhpSurfaceRunner())->run($args, $env);
}
