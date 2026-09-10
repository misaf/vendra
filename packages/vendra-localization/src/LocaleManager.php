<?php

declare(strict_types=1);

namespace Misaf\VendraLocalization;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;

final class LocaleManager
{
    public const string CONTEXT_KEY = 'locale';

    public function apply(string $locale): void
    {
        App::setLocale($locale);

        if (Config::boolean('vendra-localization.sync.carbon', false)) {
            Date::setLocale($locale);
        }

        if (Config::boolean('vendra-localization.sync.number', false)) {
            Number::useLocale($locale);
        }
    }
}
