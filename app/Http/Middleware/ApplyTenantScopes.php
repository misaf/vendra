<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Language\Language;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Language::addGlobalScope(
            fn(Builder $query) => $query->where('tenant_id', app('currentTenant')->id),
        );

        return $next($request);
    }
}
