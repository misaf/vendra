<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

/**
 * The platform is open for new stores on a fresh install; an administrator closes it
 * from the console's platform settings page.
 */
return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->repository('global');
        $this->migrator->add('store_creation.open', true);
    }
};
