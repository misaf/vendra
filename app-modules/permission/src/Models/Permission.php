<?php

declare(strict_types=1);

namespace Termehsoft\Permission\Models;

use App\Casts\DateCast;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as ModelPermission;
use Termehsoft\Tenant\Scopes\Tenant as TenantScope;

#[ScopedBy(TenantScope::class)]
final class Permission extends ModelPermission
{
    use ActivityLog;
    use BelongsToTenant;
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $casts = [
        'id'         => 'integer',
        'name'       => 'string',
        'guard_name' => 'string',
        'created_at' => DateCast::class,
        'updated_at' => DateCast::class,
        'deleted_at' => DateCast::class,
    ];

    protected $fillable = [
        'name',
        'guard_name',
    ];
}
