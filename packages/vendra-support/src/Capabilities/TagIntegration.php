<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Misaf\VendraSupport\Contracts\TagResolver;
use Misaf\VendraSupport\Support\TagRelationship;
use Throwable;

final class TagIntegration
{
    public static function isAvailable(): bool
    {
        try {
            return self::resolver()->available();
        } catch (Throwable) {
            return false;
        }
    }

    public static function relationship(): ?TagRelationship
    {
        try {
            return self::resolver()->relationship();
        } catch (Throwable) {
            return null;
        }
    }

    private static function resolver(): TagResolver
    {
        if (app()->bound(TagResolver::class)) {
            return app(TagResolver::class);
        }

        return new NullTagResolver;
    }
}
