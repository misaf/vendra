<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * The platform row every store reads until it saves general settings of its
 * own. Written with no tenant current, so it lands in the platform scope.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.site_title', Config::string('app.name'));
        $this->migrator->add('general.site_description');
    }
};
