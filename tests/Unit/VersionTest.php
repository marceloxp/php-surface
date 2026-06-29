<?php

use PhpSurface\Version;

test('version constants are defined', function () {
    expect(Version::NAME)->toBe('php-surface')
        ->and(Version::VERSION)->toBe('0.1.0-dev');
});
