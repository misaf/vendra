<?php

declare(strict_types=1);

namespace Misaf\VendraActivityLog\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\Activitylog\Models\Activity as SpatieActivityLog;

/**
 * @property int $tenant_id
 */
#[Hidden(['tenant_id'])]
final class ActivityLog extends SpatieActivityLog
{
    use BelongsToTenant;
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tenant_id' => 'integer',
            ...parent::casts(),
        ];
    }
}
