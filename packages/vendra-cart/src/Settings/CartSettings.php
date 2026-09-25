<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Settings;

use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings;

final class CartSettings extends Settings implements ShouldLogActivity
{
    /**
     * The days a new cart lives before it expires, or null to keep carts until they are removed.
     */
    public ?int $expires_after_days = null;

    public static function group(): string
    {
        return 'cart';
    }
}
