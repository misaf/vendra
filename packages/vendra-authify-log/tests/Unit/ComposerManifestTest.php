<?php

declare(strict_types=1);

it('resolves the user model through auth config instead of requiring vendra-user', function (): void {
    $manifest = json_decode((string) file_get_contents(__DIR__.'/../../composer.json'), true);

    expect($manifest['require'])->not->toHaveKey('misaf/vendra-user')
        ->and($manifest['require-dev'] ?? [])->not->toHaveKey('misaf/vendra-user');
});
