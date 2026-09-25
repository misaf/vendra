<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Settings;

use Spatie\LaravelSettings\Settings;

final class WishlistSettings extends Settings
{
    public string $default_name;

    public static function group(): string
    {
        return 'wishlist';
    }
}
