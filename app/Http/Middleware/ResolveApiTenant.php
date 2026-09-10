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
 * Resolves the tenant an API request belongs to.
 *
 * Every other surface is reached on a host that names its tenant, so the host
 * is the identifier. The canonical API is the exception: one host serves every
 * store, and the caller is a storefront on a customer domain. There the
 * request origin selects the tenant, matched against the same active tenant
 * domains that produce the CORS allowlist — so onboarding a store needs no
 * second registration.
 */
final readonly class ResolveApiTenant
{
    public function __construct(
        private StoreDomainFinder $tenantFinder,
        private TenantResolver $tenantResolver,
    ) {}

    /**
     * Handle an incoming request.
     *
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

        // A browser sends Origin on cross-origin calls; a storefront rendering
        // on the server sends it explicitly. Referer is the fallback for
        // navigations that carry no Origin.
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

    /**
     * Origin-based resolution is confined to the canonical API host so that the
     * panel host shapes keep failing closed on an unknown host.
     */
    private function isCanonicalApiHost(Request $request): bool
    {
        return Str::lower($request->getHost())
            === 'api.'.config()->string('vendra-tenant.central_host');
    }
}
