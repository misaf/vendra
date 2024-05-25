<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Models\Tenant;
use App\Models\User\Enums\UserProfileDocumentStatusEnum;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class UserProfileDocument extends Tenant implements
    Contracts\BelongsToUserProfile,
    Contracts\BelongsToUserThroughUserProfile
{
    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

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
