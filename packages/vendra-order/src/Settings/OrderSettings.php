<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Settings;

use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings;

final class OrderSettings extends Settings implements ShouldLogActivity
{
    public string $number_prefix;

    public static function group(): string
    {
        return 'order';
    }
}
