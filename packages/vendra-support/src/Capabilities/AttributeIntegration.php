<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\AttributeResolver;
use Throwable;

final class AttributeIntegration
{
    public static function isAvailable(): bool
    {
        try {
            return self::resolver()->available();
        } catch (Throwable) {
            return false;
        }
    }

    /** @return class-string<Model>|null */
    public static function valueModel(): ?string
    {
        try {
            return self::resolver()->valueModel();
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<int|string, string> */
    public static function options(): array
    {
        try {
            return self::resolver()->options();
        } catch (Throwable) {
            return [];
        }
    }

    private static function resolver(): AttributeResolver
    {
        if (app()->bound(AttributeResolver::class)) {
            return app(AttributeResolver::class);
        }

        return new NullAttributeResolver;
    }
}
