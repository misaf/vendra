<?php

declare(strict_types=1);

namespace Termehsoft\Permission\Models;

use App\Casts\DateCast;
use App\Traits\ActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role as SpatieRole;
use Termehsoft\Tenant\Traits\BelongsToTenant;

final class Role extends SpatieRole
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
