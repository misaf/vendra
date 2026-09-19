<?php

declare(strict_types=1);

namespace App\Settings;

/**
 * MySQL treats NULLs in a unique key as distinct, so the unique index uses
 * `scope` instead of the nullable `tenant_id`.
 */
final class SettingsScope
{
    public const string PLATFORM = 'global';

    private const string TENANT_PREFIX = 'tenant:';

    public static function forTenant(?int $tenantId): string
    {
        return $tenantId === null ? self::PLATFORM : self::TENANT_PREFIX.$tenantId;
    }

    public static function isPlatform(string $scope): bool
    {
        return $scope === self::PLATFORM;
    }
}
