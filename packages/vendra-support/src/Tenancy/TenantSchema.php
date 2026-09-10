<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Throwable;

/**
 * The single source of truth for the tenant foreign key.
 *
 * The column name is never hard-coded: it comes from the bound
 * {@see TenantResolver}. Vendra keeps the neutral `tenant_id`, so every
 * reusable domain package works under any tenant model; an application that
 * would rather name the column after its own tenant configures `company_id` or
 * `workspace_id` and every helper here follows.
 */
final class TenantSchema
{
    /**
     * The column used when no tenant provider is installed, and the one Vendra
     * itself keeps. It exists so provider-agnostic code has a stable answer
     * even with tenancy switched off.
     */
    public const string DEFAULT_FOREIGN_KEY = 'tenant_id';

    /**
     * @var array<string, bool>
     */
    private static array $tenantColumnCache = [];

    public static function enabled(): bool
    {
        return app(TenantResolver::class)->available();
    }

    /**
     * The tenant foreign key every tenant-scoped table carries.
     */
    public static function column(): string
    {
        if (! app()->bound(TenantResolver::class)) {
            return self::DEFAULT_FOREIGN_KEY;
        }

        return app(TenantResolver::class)->foreignKey();
    }

    public static function addTenantColumn(Blueprint $table, bool $nullable = false): void
    {
        if (! self::enabled()) {
            return;
        }

        $table->unsignedBigInteger(self::column())->nullable($nullable);
    }

    public static function addTenantIndex(Blueprint $table): void
    {
        if (! self::enabled()) {
            return;
        }

        $table->index(self::column());
    }

    /**
     * @param  string|list<string>  $columns
     * @return list<string>
     */
    public static function tenantIndex(string|array $columns): array
    {
        $columns = is_array($columns) ? $columns : [$columns];

        if (! self::enabled()) {
            return $columns;
        }

        return array_values(array_unique([self::column(), ...$columns]));
    }

    public static function hasTenantColumn(string $table): bool
    {
        $column = self::column();
        $cacheKey = "{$table}\0{$column}";

        if (array_key_exists($cacheKey, self::$tenantColumnCache)) {
            return self::$tenantColumnCache[$cacheKey];
        }

        try {
            return self::$tenantColumnCache[$cacheKey] = Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }

    public static function forgetTenantColumn(string $table): void
    {
        foreach (array_keys(self::$tenantColumnCache) as $cacheKey) {
            if (str_starts_with($cacheKey, "{$table}\0")) {
                unset(self::$tenantColumnCache[$cacheKey]);
            }
        }
    }

    public static function flushTenantColumnCache(): void
    {
        self::$tenantColumnCache = [];
    }
}
