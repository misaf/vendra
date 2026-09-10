<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

/**
 * Standard construction and lookup for Vendra panel plugins: `make()`
 * resolves a fresh instance from the container and `get()` returns the
 * instance registered on the current panel.
 */
trait ResolvesPluginInstances
{
    public static function make(): static
    {
        /** @var static $plugin */
        $plugin = resolve(static::class);

        return $plugin;
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(static::make()->getId());

        return $plugin;
    }
}
