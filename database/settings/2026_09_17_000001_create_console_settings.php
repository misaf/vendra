<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * The console's brand name on a fresh install; a console user renames the
 * platform from the console's platform settings page.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->repository('global');
        $this->migrator->add('console.platform_name', 'Vendra Console');
    }
};
