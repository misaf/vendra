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
        } catch (Throwable $exception) {
            IntegrationExceptions::report($exception);

            return false;
        }
    }

    public static function relationship(): ?TagRelationship
    {
        try {
            return self::resolver()->relationship();
        } catch (Throwable $exception) {
            IntegrationExceptions::report($exception);

            return null;
        }
    }

    private static function resolver(): TagResolver
    {
        if (app()->bound(TagResolver::class)) {
            return resolve(TagResolver::class);
        }

        return new NullTagResolver;
    }
}
