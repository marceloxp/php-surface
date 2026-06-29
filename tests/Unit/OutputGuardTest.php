<?php

use PhpSurface\Cli\OutputGuard;

test('uses default limit when env is unset', function () {
    $guard = new OutputGuard();

    expect($guard->getLimitBytes())->toBe(OutputGuard::DEFAULT_LIMIT_BYTES);
});

test('reads limit from environment', function () {
    putenv('PHP_SURFACE_MAX_OUTPUT_BYTES=32000');
    $guard = new OutputGuard();

    expect($guard->getLimitBytes())->toBe(32000);

    putenv('PHP_SURFACE_MAX_OUTPUT_BYTES');
});

test('falls back when environment value is invalid', function () {
    putenv('PHP_SURFACE_MAX_OUTPUT_BYTES=0');
    $guard = new OutputGuard();

    expect($guard->getLimitBytes())->toBe(OutputGuard::DEFAULT_LIMIT_BYTES);

    putenv('PHP_SURFACE_MAX_OUTPUT_BYTES');
});

test('allows output within limit', function () {
    $guard = new OutputGuard();

    expect($guard->isAllowed(OutputGuard::DEFAULT_LIMIT_BYTES, false))->toBeTrue()
        ->and($guard->isAllowed(OutputGuard::DEFAULT_LIMIT_BYTES + 1, false))->toBeFalse()
        ->and($guard->isAllowed(OutputGuard::DEFAULT_LIMIT_BYTES + 1, true))->toBeTrue();
});

test('formats blocked json payload', function () {
    $guard = new OutputGuard();
    $json = $guard->formatBlockedJson('File.php', 1000, 20000, 8192, ['php-surface File.php --stats']);
    $payload = json_decode($json, true);

    expect($payload['error'])->toBe(OutputGuard::ERROR_CODE)
        ->and($payload['fileBytes'])->toBe(1000)
        ->and($payload['outputBytes'])->toBe(20000)
        ->and($payload['limitBytes'])->toBe(8192)
        ->and($payload['hints'])->toHaveCount(1);
});

test('formats blocked text message', function () {
    $guard = new OutputGuard();
    $text = $guard->formatBlockedText('File.php', 1000, 20000, 8192, ['php-surface File.php --stats']);

    expect($text)->toContain('Error: output too large (20000 bytes, limit 8192).')
        ->and($text)->toContain('php-surface File.php --stats');
});
