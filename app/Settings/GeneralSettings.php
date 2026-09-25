<?php

declare(strict_types=1);

namespace App\Settings;

use Illuminate\Support\Arr;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Spatie\LaravelSettings\Settings as SpatieSettings;

/**
 * The store's name and description, keyed by locale.
 */
final class GeneralSettings extends SpatieSettings implements ShouldLogActivity
{
    /**
     * @var array<string, string>
     */
    public array $description = [];

    /**
     * @var array<string, string>
     */
    public array $name = [];

    public static function group(): string
    {
        return 'general';
    }

    /**
     * Fall back to the first saved name when the locale has none.
     */
    public function nameFor(string $locale): ?string
    {
        $names = array_filter($this->name, filled(...));

        return $names[$locale] ?? Arr::first($names);
    }
}
