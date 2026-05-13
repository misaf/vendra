<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings as SpatieSettings;

final class GeneralSettings extends SpatieSettings
{
    public ?string $site_description = null;

    public ?string $site_title = null;

    public static function group(): string
    {
        return 'general';
    }
}
