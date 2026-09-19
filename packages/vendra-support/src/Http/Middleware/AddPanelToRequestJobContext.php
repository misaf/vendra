<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Symfony\Component\HttpFoundation\Response;

final class AddPanelToRequestJobContext
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $panel = Filament::getCurrentPanel();

        if ($panel !== null) {
            new RequestJobContext(
                metadata: [ContextKeys::PANEL_ID => $panel->getId()],
            )->add();
        }

        return $next($request);
    }
}
