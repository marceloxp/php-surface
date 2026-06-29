<?php

use PhpSurface\Analyzer\StatsCollector;

test('collects symbol and method counts', function () {
    $stats = (new StatsCollector())->collect('tests/fixtures/Methods.php', [
        [
            'name' => 'OrderService',
            'namespace' => 'App\\Repo',
            'type' => 'class',
            'line' => 7,
            'methods' => [
                ['name' => 'save', 'visibility' => 'public'],
                ['name' => 'findActive', 'visibility' => 'protected', 'docblock' => ['summary' => 'Find']],
            ],
        ],
        [
            'name' => 'Readable',
            'namespace' => 'App\\Demo',
            'type' => 'interface',
            'line' => 3,
            'methods' => [
                ['name' => 'read', 'visibility' => 'public'],
            ],
        ],
    ]);

    expect($stats['file'])->toBe('tests/fixtures/Methods.php')
        ->and($stats['symbols']['total'])->toBe(2)
        ->and($stats['symbols']['byType']['class'])->toBe(1)
        ->and($stats['symbols']['byType']['interface'])->toBe(1)
        ->and($stats['methods']['total'])->toBe(3)
        ->and($stats['methods']['byVisibility']['public'])->toBe(2)
        ->and($stats['methods']['byVisibility']['protected'])->toBe(1)
        ->and($stats['methods']['withDocblock'])->toBe(1)
        ->and($stats['largest'][0]['name'])->toBe('OrderService')
        ->and($stats['largest'][0]['methods'])->toBe(2);
});
