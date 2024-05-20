<?php

declare(strict_types=1);

namespace App\Settings;

use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Spatie\LaravelSettings\Settings;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class GlobalSetting extends Settings
{
    public ?string $site_description;

    // public ?string $site_sidebar_logo_dark;

    // public ?string $site_sidebar_logo_light;

    public bool $site_status;

    public ?string $site_tags;

    public ?string $site_title;

    public static function group(): string
    {
        return 'global';
    }
}
