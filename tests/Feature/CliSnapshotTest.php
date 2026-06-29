<?php

use PhpSurface\Cli\ExitCode;

describe('json output', function () {
    test('symbols fixture', function () {
        $result = runPhpSurface([fixture('Symbols.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('methods fixture', function () {
        $result = runPhpSurface([fixture('Methods.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('docblocks fixture', function () {
        $result = runPhpSurface([fixture('Docblocks.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('empty fixture', function () {
        $result = runPhpSurface([fixture('Empty.php')]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });
});

describe('text output', function () {
    test('docblocks fixture', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->stdout)->toMatchSnapshot();
    });

    test('methods fixture', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->stdout)->toMatchSnapshot();
    });
});

describe('filter output', function () {
    test('docblocks fixture by charge', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--filter', 'charge']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('docblocks fixture by charge as text', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--filter', 'charge', '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->stdout)->toMatchSnapshot();
    });

    test('methods fixture by find', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--filter', 'find']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });
});

describe('visibility output', function () {
    test('methods fixture public', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'public']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('methods fixture protected with find filter', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'protected', '--filter', 'find']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('methods fixture public as text', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--visibility', 'public', '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->stdout)->toMatchSnapshot();
    });
});

describe('full output', function () {
    test('docblocks fixture', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--full']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('methods fixture', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--full']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('docblocks fixture with pending filter', function () {
        $result = runPhpSurface([fixture('Docblocks.php'), '--full', '--filter', 'pending']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('docblocks fixture with protected visibility and pending filter', function () {
        $result = runPhpSurface([
            fixture('Docblocks.php'),
            '--full',
            '--visibility',
            'protected',
            '--filter',
            'pending',
        ]);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });
});

describe('show output', function () {
    test('methods fixture save', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--show', 'save']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('methods fixture OrderService::findActive', function () {
        $result = runPhpSurface([fixture('Methods.php'), '--show', 'OrderService::findActive']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('symbols fixture read', function () {
        $result = runPhpSurface([fixture('Symbols.php'), '--show', 'read']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('no execute fixture ping', function () {
        $result = runPhpSurface([fixture('NoExecute.php'), '--show', 'ping']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });
});

describe('stats output', function () {
    test('monster fixture', function () {
        $result = runPhpSurface([fixture('Monster.php'), '--stats']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->json())->toMatchSnapshot();
    });

    test('monster fixture as text', function () {
        $result = runPhpSurface([fixture('Monster.php'), '--stats', '--text']);

        expect($result->exitCode)->toBe(ExitCode::SUCCESS);
        expect($result->stdout)->toMatchSnapshot();
    });
});
