<?php

declare(strict_types=1);

use Illuminate\Support\Arr;

$featureNamespaces = [];

foreach (glob(__DIR__.'/../../packages/*/composer.json') ?: [] as $manifestPath) {
    $manifest = json_decode(file_get_contents($manifestPath), true, flags: JSON_THROW_ON_ERROR);

    if (Arr::get($manifest, 'name') === 'misaf/vendra-support') {
        continue;
    }

    foreach (array_keys(Arr::get($manifest, 'autoload.psr-4', [])) as $namespace) {
        $featureNamespaces[] = mb_rtrim($namespace, '\\');
    }
}

arch()->preset()->php();
arch()->preset()->security();
arch()->preset()->laravel();

arch('vendra packages never depend on the host application')
    ->expect('Misaf')
    ->not->toUse('App');

arch('the host app declares strict types everywhere')
    ->expect('App')
    ->toUseStrictTypes();

arch('support stays independent of every feature package')
    ->expect('Misaf\VendraSupport')
    ->not->toUse($featureNamespaces);
