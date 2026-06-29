<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use PhpSurface\Cli\Application;

exit((new Application())->run($argv));
