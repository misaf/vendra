<?php

declare(strict_types=1);

namespace App\Models;

use Misaf\VendraTenant\Traits\BelongsToTenant;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\LaravelSettings\Models\SettingsProperty as SpatieSettingsProperty;

final class SettingsProperty extends SpatieSettingsProperty
{
    use BelongsToTenant;
    use LogsActivity;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'tenant_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logExcept(['id']);
    }
}
