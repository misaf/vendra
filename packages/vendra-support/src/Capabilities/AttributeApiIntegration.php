<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Misaf\VendraSupport\Contracts\AttributeApiResolver;
use Throwable;

/**
 * Resolves the optional misaf/vendra-attribute-api integration.
 *
 * Attribute-value schemas, relationships, filters, and include paths are only
 * exposed when the API package is installed *and* the domain module
 * (vendra-attribute) is registered; consumers must remain fully functional
 * without either.
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
            return app(AttributeApiResolver::class);
        }

        return new NullAttributeApiResolver;
    }
}
