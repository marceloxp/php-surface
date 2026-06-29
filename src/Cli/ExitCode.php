<?php

declare(strict_types=1);

namespace PhpSurface\Cli;

final class ExitCode
{
    public const SUCCESS = 0;

    public const USAGE = 1;

    public const FILE_ERROR = 2;

    public const OUTPUT_TOO_LARGE = 3;
}
