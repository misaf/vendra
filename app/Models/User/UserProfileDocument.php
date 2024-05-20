<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Support\Enums\UserProfileDocumentStatusEnum;
use App\Traits\BelongsToTenant;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\ModelStatus\HasStatuses;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

final class UserProfileDocument extends Model implements HasMedia
{
    use BelongsToTenant;

    use HasFactory;

    // use HasStatuses;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use LogsActivity;

    use SoftDeletes;

    use TraitsBelongsToThrough;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }

    public function user(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\User::class,
            through: \App\Models\User\UserProfile::class,
        );
    }

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\User\UserProfile::class,
        );
    }
}
