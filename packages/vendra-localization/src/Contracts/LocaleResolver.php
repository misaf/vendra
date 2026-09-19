<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Contracts;

use Illuminate\Http\Request;

interface LocaleResolver
{
    /**
     * Resolve the request's preferred locale, or null when this source has none.
     */
    public function resolve(Request $request): ?string;
}
