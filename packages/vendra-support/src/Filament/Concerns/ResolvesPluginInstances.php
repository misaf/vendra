<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

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
