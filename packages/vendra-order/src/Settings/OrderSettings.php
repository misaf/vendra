<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Settings;

use Spatie\LaravelSettings\Settings;

final class OrderSettings extends Settings
{
    public string $number_prefix;

    public static function group(): string
    {
        return 'order';
    }
}
