<?php

declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('delivery.advance_days', 14);
        $this->migrator->add('delivery.same_day_cutoff_hour', 14);
    }
};
