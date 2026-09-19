<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Misaf\VendraStore\Services\StoreDomainFinder;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Spatie\Multitenancy\Contracts\IsTenant;
use Symfony\Component\HttpFoundation\Response;

/**
 * The canonical API serves every store on one host, so there the request
 * origin selects the tenant instead of the host.
 */
final readonly class ResolveApiTenant
{
    public function __construct(
        private StoreDomainFinder $tenantFinder,
        private TenantResolver $tenantResolver,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var IsTenant|null $resolvedTenant */
        $resolvedTenant = null;

        if ($this->tenantResolver->current() === null) {
            $tenant = $this->resolveTenant($request);

            abort_if($tenant === null, Response::HTTP_NOT_FOUND);

            $tenant->makeCurrent();
            $resolvedTenant = $tenant;
        }

        try {
            return $next($request);
        } finally {
            $resolvedTenant?->forget();
        }
    }

    private function resolveTenant(Request $request): ?IsTenant
    {
        $tenant = $this->tenantFinder->findForRequest($request);

        if ($tenant instanceof IsTenant || ! $this->isCanonicalApiHost($request)) {
            return $tenant;
        }

        // Prefer Origin, falling back to Referer for navigations without one.
        foreach ([$request->headers->get('Origin'), $request->headers->get('Referer')] as $origin) {
            if (! is_string($origin) || $origin === '') {
                continue;
            }

            $tenant = $this->tenantFinder->findForOrigin($origin);

            if ($tenant instanceof IsTenant) {
                return $tenant;
            }
        }

        return null;
    }

    private function isCanonicalApiHost(Request $request): bool
    {
        return Str::lower($request->getHost())
            === 'api.'.config()->string('vendra-tenant.central_host');
    }
}
