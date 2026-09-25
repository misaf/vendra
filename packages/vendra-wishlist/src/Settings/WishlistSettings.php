<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Settings;

use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings;

final class WishlistSettings extends Settings implements ShouldLogActivity
{
    public string $default_name;

    public static function group(): string
    {
        return 'wishlist';
    }
}
