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
 * Matches an active store domain, `<slug>.admin.<central host>`, or `admin.`
 * in front of a store domain.
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
     * Resolve the store that owns a storefront origin, such as "https://shop.com".
     *
     * Admin hosts are not accepted as origins.
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
