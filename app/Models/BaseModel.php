<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\ActivityLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Termehsoft\Tenant;

class BaseModel extends Model
{
    use ActivityLog;
    use HasFactory;
    use LogsActivity;
    use Tenant\Traits\BelongsToTenant;
}
