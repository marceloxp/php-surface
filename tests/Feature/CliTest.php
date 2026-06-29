<?php

use PhpSurface\Cli\ExitCode;
use PhpSurface\Cli\OutputGuard;
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

describe('output guard', function () {
    test('blocks oversized default map output with json error', function () {
        $result = runPhpSurface([fixture('Monster.php')]);

        expect($result->exitCode)->toBe(ExitCode::OUTPUT_TOO_LARGE)
            ->and($result->stdout)->toBe('');

        $error = json_decode($result->stderr, true);
        expect($error['error'])->toBe('output_too_large')
            ->and($error['outputBytes'])->toBeGreaterThan($error['limitBytes'])
            ->and($error['hints'])->toContain('php-surface tests/fixtures/Monster.php --stats')
            ->and($error['hints'])->toContain('php-surface tests/fixtures/Monster.php --allow-large-output');
    });

    test('blocks oversized output with text error when --text is used', function () {
        $result = runPhpSurface(
            [fixture('Monster.php'), '--text'],
            ['PHP_SURFACE_MAX_OUTPUT_BYTES' => '10000'],
        );

        expect($result->exitCode)->toBe(ExitCode::OUTPUT_TOO_LARGE)
            ->and($result->stdout)->toBe('')
            ->and($result->stderr)->toContain('Error: output too large')
            ->and($result->stderr)->toContain('php-surface tests/fixtures/Monster.php --stats');
    });

    test('blocks oversized show output', function () {
        $result = runPhpSurface(
            [fixture('Monster.php'), '--show', 'LargeMethodExample::largeMethodWithComplexControlFlow'],
            ['PHP_SURFACE_MAX_OUTPUT_BYTES' => '1000'],
        );

        expect($result->exitCode)->toBe(ExitCode::OUTPUT_TOO_LARGE)
            ->and($result->stdout)->toBe('');

        $error = json_decode($result->stderr, true);
        expect($error['error'])->toBe('output_too_large')
            ->and($error['hints'])->toContain(
                'php-surface tests/fixtures/Monster.php --allow-large-output --show LargeMethodExample::largeMethodWithComplexControlFlow'
            );
    });

    test('allow-large-output bypasses guard', function () {
        $result = runPhpSurface([fixture('Monster.php'), '--allow-large-output']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and(strlen($result->stdout))->toBeGreaterThan(OutputGuard::DEFAULT_LIMIT_BYTES);
    });

    test('custom max output bytes via environment', function () {
        $result = runPhpSurface([fixture('Monster.php')], [
            'PHP_SURFACE_MAX_OUTPUT_BYTES' => '50000',
        ]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and(strlen($result->stdout))->toBeGreaterThan(OutputGuard::DEFAULT_LIMIT_BYTES);
    });

    test('small fixtures still pass through guard', function () {
        $result = runPhpSurface([fixture('Symbols.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->not->toBe('');
    });
});

describe('filter', function () {
    test('drops symbols without matching methods', function () {
        $result = runPhpSurface([fixture('Monster.php'), '--filter', 'nested']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $data = $result->json();
        expect($data['symbols'])->toHaveCount(1)
            ->and($data['symbols'][0]['name'])->toBe('ClosureExample')
            ->and($data['symbols'][0]['methods'])->toHaveCount(1)
            ->and($data['symbols'][0]['methods'][0]['name'])->toBe('nestedClosure');
    });
});

describe('search', function () {
    test('finds nested constructs in monster fixture', function () {
        $result = runPhpSurface([fixture('Monster.php'), '--search', 'nested']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $data = $result->json();
        expect($data['query'])->toBe('nested')
            ->and($data['matches'])->not->toBeEmpty();

        $kinds = array_column($data['matches'], 'kind');
        expect($kinds)->toContain('symbol', 'method', 'source');

        $symbol = null;
        foreach ($data['matches'] as $match) {
            if ($match['kind'] === 'symbol' && $match['name'] === 'NestedConstructsExample') {
                $symbol = $match;
                break;
            }
        }

        expect($symbol)->not->toBeNull()
            ->and($symbol['hint'])->toBe('php-surface tests/fixtures/Monster.php --show NestedConstructsExample::complexMethod');
    });

    test('renders text output', function () {
        $result = runPhpSurface([fixture('Symbols.php'), '--search', 'read', '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toContain('query: read')
            ->and($result->stdout)->toContain('Readable');
    });
});
