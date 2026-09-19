<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Throwable;

final class TenantSchema
{
    /**
     * The foreign key used when no tenant provider is installed.
     */
    public const string DEFAULT_FOREIGN_KEY = 'tenant_id';

    /**
     * @var array<string, bool>
     */
    private static array $tenantColumnCache = [];

    public static function enabled(): bool
    {
        return resolve(TenantResolver::class)->available();
    }

    public static function column(): string
    {
        if (! app()->bound(TenantResolver::class)) {
            return self::DEFAULT_FOREIGN_KEY;
        }

        return resolve(TenantResolver::class)->foreignKey();
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
