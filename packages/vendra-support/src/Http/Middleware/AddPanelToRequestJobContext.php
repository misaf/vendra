<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the current Filament panel id to the request context.
 *
 * It lives beside {@see ContextKeys} because every panel package stamps the same
 * key and none of them depend on each other. The actor identity is attached once
 * at the authentication boundary instead, which also covers non-panel guards.
 */
final class AddPanelToRequestJobContext
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();

        if ($panel !== null) {
            (new RequestJobContext(
                metadata: [ContextKeys::PANEL_ID => $panel->getId()],
            ))->add();
        }

        return $next($request);
    }
}
