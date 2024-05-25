<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\Tenant as TenantScope;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(TenantScope::class)]
class Tenant extends Model
{
    use ActivityLog;
    use BelongsToTenant;
    use HasFactory;
    use LogsActivity;
}
