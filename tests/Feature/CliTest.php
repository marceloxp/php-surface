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

describe('symbols', function () {
    test('extracts symbols from fixture', function () {
        $result = runPhpSurface([fixture('Symbols.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $data = $result->json();
        expect($data['symbols'])->toHaveCount(3)
            ->and($data['symbols'][0]['name'])->toBe('Readable');
    });

    test('output is deterministic', function () {
        $args = [fixture('Symbols.php')];
        $first = runPhpSurface($args);
        $second = runPhpSurface($args);

        expect($first->stdout)->toBe($second->stdout);
    });
});

describe('methods', function () {
    test('extracts lean methods from fixture', function () {
        $result = runPhpSurface([fixture('Methods.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $data = $result->json();
        expect($data['symbols'][0]['methods'])->not->toBeEmpty();
    });

    test('output is deterministic', function () {
        $args = [fixture('Methods.php')];
        $first = runPhpSurface($args);
        $second = runPhpSurface($args);

        expect($first->stdout)->toBe($second->stdout);
    });
});

describe('docblocks', function () {
    test('extracts docblocks from fixture', function () {
        $result = runPhpSurface([fixture('Docblocks.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $data = $result->json();
        expect($data['symbols'][0]['methods'][0]['docblock']['summary'])->toContain('pagamento');
    });
});

describe('--text', function () {
    test('renders docblocks fixture as text', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toContain('class App\\Svc\\PaymentGateway');
    });

    test('renders methods fixture as text', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toContain('class App\\Repo\\OrderService');
    });
});

describe('--filter', function () {
    test('filters docblocks fixture by method name', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--filter', 'charge']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $data = $result->json();
        expect($data['symbols'][0]['methods'])->toHaveCount(1)
            ->and($data['symbols'][0]['methods'][0]['name'])->toBe('charge');
    });

    test('filters docblocks fixture as text', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--filter', 'charge', '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toContain('function charge')
            ->and($result->stdout)->not->toContain('function refund');
    });

    test('filters methods fixture by method name', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--filter', 'find']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $methods = array_merge(...array_map(
            fn (array $symbol) => $symbol['methods'],
            $result->json()['symbols'],
        ));
        expect($methods)->not->toBeEmpty();
        expect(array_filter($methods, fn (array $method) => !str_contains($method['name'], 'find')))->toBeEmpty();
    });
});

describe('--visibility', function () {
    test('filters methods by public visibility', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'public']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $methods = array_merge(...array_map(
            fn (array $symbol) => $symbol['methods'],
            $result->json()['symbols'],
        ));
        expect(array_filter($methods, fn (array $method) => $method['visibility'] !== 'public'))->toBeEmpty();
    });

    test('combines protected visibility with filter', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'protected', '--filter', 'find']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $methods = array_merge(...array_map(
            fn (array $symbol) => $symbol['methods'],
            $result->json()['symbols'],
        ));
        expect($methods)->not->toBeEmpty();
        expect(array_filter($methods, fn (array $method) => $method['visibility'] !== 'protected'))->toBeEmpty();
    });

    test('renders public methods as text', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'public', '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->stdout)->toContain('public function');
    });

    test('rejects invalid visibility', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'invalid']);

        expect($result->exitCode)->toBe(ExitCode::USAGE)
            ->and($result->stderr)->toContain('--visibility must be one of');
    });
});

describe('--full', function () {
    test('renders docblocks fixture in full mode', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--full']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['symbols'])->not->toBeEmpty();
    });

    test('renders methods fixture in full mode', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--full']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['symbols'])->not->toBeEmpty();
    });

    test('combines full mode with filter', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--full', '--filter', 'pending']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $methods = array_merge(...array_map(
            fn (array $symbol) => $symbol['methods'],
            $result->json()['symbols'],
        ));
        expect($methods)->toHaveCount(1)
            ->and($methods[0]['name'])->toBe('pending');
    });

    test('default mode still works alongside full combinations', function () {
        $result = runPhpSurface([fixture('Docblocks.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['symbols'])->not->toBeEmpty();
    });

    test('combines full mode with visibility and filter', function () {
        $result = runPhpSurface([
            fixture('Docblocks.php'),
            '--full',
            '--visibility',
            'protected',
            '--filter',
            'pending',
        ]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);

        $methods = array_merge(...array_map(
            fn (array $symbol) => $symbol['methods'],
            $result->json()['symbols'],
        ));
        expect($methods)->toHaveCount(1)
            ->and($methods[0]['name'])->toBe('pending')
            ->and($methods[0]['visibility'])->toBe('protected');
    });
});

describe('--show', function () {
    test('shows method by name', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--show', 'save']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['matches'][0]['method'])->toBe('save');
    });

    test('shows method by qualified name', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--show', 'OrderService::findActive']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['matches'][0]['method'])->toBe('findActive');
    });

    test('shows interface method', function () {
        $result = runPhpSurface([fixture('Symbols.php'), '--show', 'read']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['matches'][0]['method'])->toBe('read');
    });

    test('shows method without execute permission', function () {
        $result = runPhpSurface([fixture('NoExecute.php'), '--show', 'ping']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['matches'][0]['method'])->toBe('ping');
    });

    test('fails when symbol does not exist', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--show', 'nonexistent']);

        expect($result->exitCode)->toBe(ExitCode::USAGE)
            ->and($result->stderr)->toContain('no symbol matched');
    });
});

describe('robustness', function () {
    test('handles empty php file', function () {
        $result = runPhpSurface([fixture('Empty.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
    });

    test('fails on syntax error', function () {
        $result = runPhpSurface([fixture('SyntaxError.php')]);

        expect($result->exitCode)->toBe(ExitCode::FILE_ERROR)
            ->and($result->stderr)->toContain('Syntax error');
    });

    test('methods output remains deterministic', function () {
        $args = [fixture('Methods.php')];
        $first = runPhpSurface($args);
        $second = runPhpSurface($args);

        expect($first->stdout)->toBe($second->stdout);
    });

    test('works with PHP_SURFACE_BIN_PATH override', function () {
        $result = runPhpSurface([fixture('Methods.php')], [
            'PHP_SURFACE_BIN_PATH' => PHP_BINARY,
        ]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS)
            ->and($result->json()['symbols'])->not->toBeEmpty();
    });
});
