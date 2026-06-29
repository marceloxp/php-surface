<?php

use PhpSurface\Filter\MethodNameFilter;

test('keeps only symbols with matching methods', function () {
    $symbols = [
        [
            'name' => 'OrderService',
            'type' => 'class',
            'methods' => [
                ['name' => 'findActive'],
                ['name' => 'save'],
            ],
        ],
        [
            'name' => 'Readable',
            'type' => 'interface',
            'methods' => [
                ['name' => 'read'],
            ],
        ],
        [
            'name' => 'EmptyMatch',
            'type' => 'class',
            'methods' => [
                ['name' => 'ping'],
            ],
        ],
    ];

    $filtered = (new MethodNameFilter())->apply($symbols, 'find');

    expect($filtered)->toHaveCount(1)
        ->and($filtered[0]['name'])->toBe('OrderService')
        ->and($filtered[0]['methods'])->toHaveCount(1)
        ->and($filtered[0]['methods'][0]['name'])->toBe('findActive');
});
