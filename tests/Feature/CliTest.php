<?php

use PhpSurface\Cli\ExitCode;
use PhpSurface\Version;

describe('CLI', function () {
    test('--help exits successfully', function () {
        $result = runPhpSurface(['--help']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toContain('Usage:');
    });

    test('--version prints version', function () {
        $result = runPhpSurface(['--version']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toBe(Version::NAME . ' ' . Version::VERSION . PHP_EOL);
    });

    test('missing file fails', function () {
        $result = runPhpSurface(['arquivo_inexistente.php']);

        expect($result->exitCode)->toBe(ExitCode::FILE_ERROR)
            ->and($result->stderr)->toContain('file not found');
    });

    test('non-php file fails', function () {
        $result = runPhpSurface(['README.md']);

        expect($result->exitCode)->toBe(ExitCode::FILE_ERROR)
            ->and($result->stderr)->toContain('not a PHP file');
    });
});

describe('validation', function () {
    test('rejects invalid visibility', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'invalid']);

        expect($result->exitCode)->toBe(ExitCode::USAGE)
            ->and($result->stderr)->toContain('--visibility must be one of');
    });

    test('fails when show target does not exist', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--show', 'nonexistent']);

        expect($result->exitCode)->toBe(ExitCode::USAGE)
            ->and($result->stderr)->toContain('no symbol matched');
    });

    test('fails on syntax error', function () {
        $result = runPhpSurface([fixture('SyntaxError.php')]);

        expect($result->exitCode)->toBe(ExitCode::FILE_ERROR)
            ->and($result->stderr)->toContain('Syntax error');
    });
});

describe('runtime', function () {
    test('works with PHP_SURFACE_BIN_PATH override', function () {
        $result = runPhpSurface([fixture('Methods.php')], [
            'PHP_SURFACE_BIN_PATH' => PHP_BINARY,
        ]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
    });
});
