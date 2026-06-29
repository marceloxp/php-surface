<?php

use PhpSurface\Extractor\SearchExtractor;

test('finds symbols methods and source lines', function () {
    $symbols = [
        [
            'name' => 'NestedConstructsExample',
            'namespace' => 'Surface\\Monster',
            'type' => 'class',
            'line' => 1767,
            'methods' => [
                ['name' => 'complexMethod', 'startLine' => 1769],
            ],
        ],
        [
            'name' => 'ClosureExample',
            'namespace' => 'Surface\\Monster',
            'type' => 'class',
            'line' => 588,
            'methods' => [
                ['name' => 'nestedClosure', 'startLine' => 615],
            ],
        ],
    ];

    $file = tempnam(sys_get_temp_dir(), 'search');
    file_put_contents($file, "<?php\n// nested comment\nclass Foo {}\n");

    $matches = (new SearchExtractor())->search($file, $symbols, 'nested');

    unlink($file);

    $kinds = array_column($matches, 'kind');

    expect($matches)->toHaveCount(3)
        ->and($kinds)->toContain('symbol', 'method', 'source');

    $symbol = null;
    $method = null;

    foreach ($matches as $match) {
        if ($match['kind'] === 'symbol') {
            $symbol = $match;
        }

        if ($match['kind'] === 'method') {
            $method = $match;
        }
    }

    expect($symbol['name'])->toBe('NestedConstructsExample')
        ->and($symbol['hint'])->toBe('php-surface ' . $file . ' --show NestedConstructsExample::complexMethod')
        ->and($method['method'])->toBe('nestedClosure')
        ->and($method['hint'])->toContain('ClosureExample::nestedClosure');
});
