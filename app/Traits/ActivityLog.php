<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\Activitylog\LogOptions;

trait ActivityLog
{
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }
}
