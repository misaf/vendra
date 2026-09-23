<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Lives here rather than in the console because the reseller panel creates stores too.
 */
final class StoreCreationSettings extends Settings
{
    public bool $open;

    public static function group(): string
    {
        return 'store_creation';
    }

    public static function repository(): string
    {
        return 'global';
    }
}
