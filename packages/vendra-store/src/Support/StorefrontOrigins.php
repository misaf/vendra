<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Scopes\StoreScope;
use Misaf\VendraSupport\Tenancy\Scopes\TeamScope;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;

/**
 * The cache is always read and cleared in landlord context, or a clear from
 * inside a tenant would miss the shared key.
 */
final class StorefrontOrigins
{
    public const string CACHE_KEY = 'cors:storefront-origins';

    /**
     * @return list<string>
     */
    public function all(): array
    {
        /** @var list<string> $origins */
        $origins = self::asLandlord(fn (): array => Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->query()));

        return $origins;
    }

    public static function forget(): void
    {
        self::asLandlord(fn (): bool => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @template TResult
     *
     * @param  callable(): TResult  $callback
     * @return TResult
     */
    private static function asLandlord(callable $callback): mixed
    {
        $current = Store::current();

        if (! $current instanceof Store) {
            return $callback();
        }

        Store::forgetCurrent();

        try {
            return $callback();
        } finally {
            $current->makeCurrent();
        }
    }

    /**
     * @return list<string>
     */
    private function query(): array
    {
        try {
            $domains = StoreDomain::query()
                ->withoutGlobalScopes([StoreScope::class, TenantScope::class, TeamScope::class])
                ->where('active', true)
                ->pluck('name');
        } catch (QueryException) {
            // Without a database, deny every cross-origin call.
            return [];
        }

        return array_values(
            $domains
                ->flatMap(function (mixed $name): array {
                    if (! is_string($name) || $name === '') {
                        return [];
                    }

                    return [
                        'https://'.$name,
                        'https://www.'.$name,
                    ];
                })
                ->unique()
                ->all(),
        );
    }
}
