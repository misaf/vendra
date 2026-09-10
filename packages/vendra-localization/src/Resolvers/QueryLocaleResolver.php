<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Resolvers;

use Illuminate\Http\Request;
use Misaf\VendraLocalization\Contracts\LocaleResolver;

final readonly class QueryLocaleResolver implements LocaleResolver
{
    public function resolve(Request $request): ?string
    {
        $locale = $request->query('locale');

        return is_string($locale) && $locale !== '' ? $locale : null;
    }
}
