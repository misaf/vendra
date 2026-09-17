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
 * The CORS allowlist for the canonical API.
 *
 * Storefronts are served on customer domains but fetch their data from
 * https://api.<base>, so every browser call is cross-origin. The allowlist
 * therefore has to be data, not config: it changes whenever a store is
 * onboarded. A wildcard is not an option — it cannot be combined with
 * credentials, and it would let any site on the internet read the API through a
 * visitor's browser.
 *
 * The cache entry is always read and cleared in landlord context. Domains are
 * created inside `$store->execute()`, and the tenant cache prefix would
 * otherwise send the clear to a key nobody reads, leaving a new store's domain
 * blocked until the cache is flushed by hand.
 */
final class StorefrontOrigins
{
    public const string CACHE_KEY = 'cors:storefront-origins';

    /**
     * Active storefront origins, e.g. ['https://abbas.com', 'https://www.abbas.com'].
     *
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
            // No database yet (fresh install, pre-migration CI). An empty
            // allowlist denies every cross-origin call, which is the safe way to
            // fail: it never widens access.
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
