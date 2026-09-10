<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Resolvers;

use Illuminate\Http\Request;
use Misaf\VendraLocalization\Contracts\LocaleResolver;

final readonly class RouteLocaleResolver implements LocaleResolver
{
    public function resolve(Request $request): ?string
    {
        $locale = $request->route('locale');

        return is_string($locale) && $locale !== '' ? $locale : null;
    }
}
