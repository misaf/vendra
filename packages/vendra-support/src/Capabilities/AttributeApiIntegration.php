<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Misaf\VendraSupport\Contracts\AttributeApiResolver;
use Throwable;

/**
 * Attribute API features are only exposed when both the API and domain packages
 * are installed; consumers must work without them.
 */
final class AttributeApiIntegration
{
    public static function isAvailable(): bool
    {
        try {
            return AttributeIntegration::isAvailable() && self::resolver()->isAvailable();
        } catch (Throwable) {
            return false;
        }
    }

    /** @return class-string|null */
    public static function attributeOptionResource(): ?string
    {
        try {
            return self::resolver()->attributeOptionResource();
        } catch (Throwable) {
            return null;
        }
    }

    private static function resolver(): AttributeApiResolver
    {
        if (app()->bound(AttributeApiResolver::class)) {
            return resolve(AttributeApiResolver::class);
        }

        return new NullAttributeApiResolver;
    }
}
