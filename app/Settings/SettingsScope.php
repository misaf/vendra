<?php

declare(strict_types=1);

namespace App\Settings;

/**
 * The scope key a settings row is stored under.
 *
 * A settings row belongs either to the platform or to exactly one tenant, and
 * `settings.tenant_id` says which — `null` for the platform. That column alone
 * cannot carry the uniqueness a repeated save depends on: MySQL treats every
 * NULL in a unique key as distinct, so `(tenant_id, group, name)` would let a
 * second platform row for the same property be inserted on every save. The
 * `settings.scope` column is the non-null projection of the same fact, and it
 * is what the unique index and every upsert conflict target are built on.
 */
final class SettingsScope
{
    /**
     * The scope every platform-wide settings row carries.
     */
    public const string PLATFORM = 'global';

    private const string TENANT_PREFIX = 'tenant:';

    /**
     * The scope key for a tenant, or the platform scope when there is none.
     */
    public static function forTenant(?int $tenantId): string
    {
        return $tenantId === null ? self::PLATFORM : self::TENANT_PREFIX.$tenantId;
    }

    public static function isPlatform(string $scope): bool
    {
        return $scope === self::PLATFORM;
    }
}
