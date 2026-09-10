<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Contracts\HostTenantFinder;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder as SpatieTenantFinder;

/**
 * Resolves the current store from the request host.
 *
 * This is the ecommerce adapter behind the engine's {@see HostTenantFinder}
 * port: a store owns its domains, so only this package knows how a host maps to
 * a tenant. It is also the `tenant_finder` Spatie itself calls.
 *
 * Two host shapes resolve a store: one of its own active domains, and its
 * administration host — either `<slug>.admin.<central host>` or `admin.` in
 * front of one of its domains.
 */
final class StoreDomainFinder extends SpatieTenantFinder implements HostTenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        return $this->findForHost($request->getHost());
    }

    public function findForHost(string $host): ?IsTenant
    {
        return $this->findForAdminHost($host) ?? $this->findForStoreDomain($host);
    }

    public function findForAdminHost(string $host): ?IsTenant
    {
        $host = Str::lower($host);
        $adminDomain = 'admin.'.config()->string('vendra-tenant.central_host');

        if (Str::endsWith($host, '.'.$adminDomain)) {
            $storeSlug = Str::beforeLast($host, '.'.$adminDomain);

            if ($storeSlug !== '' && ! str_contains($storeSlug, '.')) {
                return Store::query()
                    ->accessible()
                    ->where('slug', $storeSlug)
                    ->first();
            }
        }

        if (Str::startsWith($host, 'admin.')) {
            return $this->findForStoreDomain(Str::after($host, 'admin.'));
        }

        return null;
    }

    /**
     * Resolve the store that owns a storefront origin, e.g. "https://shop.com".
     *
     * The canonical API answers on one host for every store, so a storefront
     * identifies itself by the origin it calls from — the same active store
     * domain data that backs the CORS allowlist. Admin host shapes are
     * deliberately not accepted here: an origin is not an admin surface.
     */
    public function findForOrigin(string $origin): ?IsTenant
    {
        $host = parse_url($origin, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        return $this->findForStoreDomain($host);
    }

    private function findForStoreDomain(string $host): ?IsTenant
    {
        return Store::query()
            ->accessible()
            ->whereHas('storeDomains', fn (Builder $query): Builder => $query
                ->where('name', Str::lower($host))
                ->where('active', true))
            ->first();
    }
}
