<?php

declare(strict_types=1);

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

it('round-trips the classes Pulse dashboard cards cache', function (): void {
    Cache::store()->put('pulse-probe', collect([(object) ['hits' => 1]]), 30 * 60);

    $value = Cache::store()->get('pulse-probe');

    expect($value)->toBeInstanceOf(Collection::class)
        ->and($value->first())->toBeInstanceOf(stdClass::class)
        ->and($value->first()->hits)->toBe(1);
});

/*
 | The metadata of every registered API resource has to survive a round trip
 | through the cache store, because api-platform reads it back from there on the
 | next boot. The suite itself caches into the array store, which never
 | serializes, so this asserts against a store that does. `unserialize()`
 | rejects any class in the graph that is not
 | allow-listed in `config/cache.php`, and the rejection is silent: the value
 | comes back as an incomplete object and only explodes when a factory calls a
 | method on it, during boot, long after the resource was written. One
 | unlisted operation class takes the whole application down.
 */
it('round-trips the metadata of every API resource through the cache store', function (): void {
    $names = resolve(ResourceNameCollectionFactoryInterface::class)->create();
    $factory = resolve(ResourceMetadataCollectionFactoryInterface::class);

    foreach ($names as $resourceClass) {
        Cache::store('file')->put('metadata-probe', $factory->create($resourceClass), 30 * 60);

        $restored = Cache::store('file')->get('metadata-probe');

        expect(incompleteClassesIn($restored))
            ->toBe([], "The cached metadata of [{$resourceClass}] came back incomplete.");
    }
});

/**
 * Every class in an object graph that `unserialize()` refused to restore.
 *
 * @return list<string>
 */
function incompleteClassesIn(mixed $value, int $depth = 0): array
{
    if ($depth > 12) {
        return [];
    }

    if ($value instanceof __PHP_Incomplete_Class) {
        $name = Arr::get((array) $value, '__PHP_Incomplete_Class_Name', 'unknown');

        return [is_string($name) ? $name : 'unknown'];
    }

    if (is_iterable($value) || is_object($value)) {
        $found = [];

        foreach ((array) $value as $item) {
            $found = [...$found, ...incompleteClassesIn($item, $depth + 1)];
        }

        return $found;
    }

    return [];
}
