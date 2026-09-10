<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

it('resolves the user model through auth config instead of requiring vendra-user', function (): void {
    $manifest = json_decode((string) file_get_contents(__DIR__.'/../../composer.json'), true);

    expect(Arr::get($manifest, 'require'))->not->toHaveKey('misaf/vendra-user')
        ->and(Arr::get($manifest, 'require-dev', []))->not->toHaveKey('misaf/vendra-user');
});
