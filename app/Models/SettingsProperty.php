<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\LaravelSettings\Models\SettingsProperty as SpatieSettingsProperty;

#[Hidden([
    'scope',
    'tenant_id',
])]
final class SettingsProperty extends SpatieSettingsProperty
{
    use BelongsToTenant;
    use HasFactory;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logExcept(['id']);
    }
}
