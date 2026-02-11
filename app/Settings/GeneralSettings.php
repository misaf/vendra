<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings as SpatieSettings;

final class GeneralSettings extends SpatieSettings
{
    public ?string $site_description;

    public ?string $site_title;

    /**
     * @return string
     */
    public static function group(): string
    {
        return 'general';
    }
}
