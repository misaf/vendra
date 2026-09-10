<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Monolog\Handler\NullHandler;

it('discards expected exception reports while running tests', function (): void {
    expect(config('logging.default'))->toBe('testing')
        ->and(config('logging.channels.testing.handler'))->toBe(NullHandler::class);
});

it('runs the host test script in parallel without changing diagnostic scripts', function (): void {
    $manifest = json_decode(
        file_get_contents(base_path('composer.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $testCommand = implode(' ', (array) (Arr::get($manifest, 'scripts.test', [])));

    expect($testCommand)->toContain('--parallel');

    foreach (Arr::get($manifest, 'scripts', []) as $scriptName => $commands) {
        if (preg_match('/coverage|profil|mutation|benchmark/i', $scriptName) !== 1) {
            continue;
        }

        expect(implode(' ', (array) $commands))->not->toContain('--parallel');
    }
});

it('boots Laravel only for feature tests', function (): void {
    $configuration = file_get_contents(base_path('tests/Pest.php'));

    expect($configuration)
        ->toContain("'Feature'")
        ->toContain("'../packages/*/tests/Feature'")
        ->not->toContain("'Unit'")
        ->not->toContain("'../packages/*/tests',");
});
