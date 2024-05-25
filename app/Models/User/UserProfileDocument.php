<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\User\Enums\UserProfileDocumentStatusEnum;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\InteractsWithMedia;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

#[ScopedBy(TenantScope::class)]
final class UserProfileDocument extends Model implements
    Contracts\BelongsToUserProfile,
    Contracts\BelongsToUserThroughUserProfile
{
    use ActivityLog;

    use BelongsToTenant;

    use HasFactory;

    // use HasStatuses;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use LogsActivity;

    use SoftDeletes;

    use TraitBelongsToThrough;

    use Traits\BelongsToUserProfile;

    use Traits\BelongsToUserThroughUserProfile;

    protected $casts = [
        'id'              => 'integer',
        'user_profile_id' => 'integer',
        'status'          => UserProfileDocumentStatusEnum::class,
        'verified_at'     => 'datetime',
        'created_at'      => DateCast::class,
        'updated_at'      => DateCast::class,
        'deleted_at'      => DateCast::class,
    ];

    protected $fillable = [
        'user_profile_id',
        'status',
        'verified_at',
    ];
}
