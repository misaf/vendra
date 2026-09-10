<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Resolvers;

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Http\Request;
use Misaf\VendraLocalization\Contracts\LocaleResolver;

final readonly class UserLocaleResolver implements LocaleResolver
{
    public function resolve(Request $request): ?string
    {
        $user = $request->user();

        $locale = $user instanceof HasLocalePreference
            ? $user->preferredLocale()
            : data_get($user, 'locale');

        return is_string($locale) && $locale !== '' ? $locale : null;
    }
}
