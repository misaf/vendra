<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization\Resolvers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Contracts\ProvidesVaryHeaders;

final readonly class AcceptLanguageLocaleResolver implements LocaleResolver, ProvidesVaryHeaders
{
    public function resolve(Request $request): ?string
    {
        $header = (string) $request->headers->get('Accept-Language');

        if ($header === '') {
            return null;
        }

        return $request->getPreferredLanguage(array_values(array_filter(
            Config::array('vendra-localization.supported_locales', []),
            is_string(...),
        )));
    }

    public function varyHeaders(): array
    {
        return ['Accept-Language'];
    }
}
