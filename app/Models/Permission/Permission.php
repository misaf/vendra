<?php

declare(strict_types=1);

namespace App\Models\Permission;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission as ModelPermission;

#[ScopedBy(TenantScope::class)]
final class Permission extends ModelPermission
{
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }
}
