<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * Create the platform defaults every store reads until it saves its own.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_title', Config::string('app.name'));
        $this->migrator->add('general.site_description');
    }
};
