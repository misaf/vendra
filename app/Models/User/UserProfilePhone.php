<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\User\Enums\UserProfileDocumentStatusEnum;
use App\Models\User\Enums\UserProfilePhoneStatusEnum;
use App\Models\User\Services\UserProfilePhoneService;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStatus\HasStatuses;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

#[ScopedBy(TenantScope::class)]
final class UserProfilePhone extends Model implements
    Contracts\BelongsToUserProfile,
    Contracts\BelongsToUserThroughUserProfile
{
    use ActivityLog;

    use BelongsToTenant;

    use HasFactory;

    // use HasStatuses;

    use LogsActivity;

    use SoftDeletes;

    use TraitBelongsToThrough;

    use Traits\BelongsToUserProfile;

    use Traits\BelongsToUserThroughUserProfile;

    protected $casts = [
        'id'               => 'integer',
        'user_profile_id'  => 'integer',
        'country'          => 'string',
        'phone'            => RawPhoneNumberCast::class . ':country',
        'phone_normalized' => 'string',
        'phone_national'   => 'string',
        'phone_e164'       => 'string',
        'status'           => UserProfileDocumentStatusEnum::class,
        'verified_at'      => 'datetime',
        'created_at'       => DateCast::class,
        'updated_at'       => DateCast::class,
        'deleted_at'       => DateCast::class,
    ];

    protected $fillable = [
        'user_profile_id',
        'country',
        'phone',
        'phone_normalized',
        'phone_national',
        'phone_e164',
        'status',
        'verified_at',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function (UserProfilePhone $userProfilePhone): void {
            if ($userProfilePhone->isDirty('phone') && $userProfilePhone->phone) {
                $userProfilePhone->phone_normalized = UserProfilePhoneService::phoneNormalized($userProfilePhone->phone);
                $userProfilePhone->phone_national = UserProfilePhoneService::phoneNational($userProfilePhone->country, $userProfilePhone->phone);
                $userProfilePhone->phone_e164 = UserProfilePhoneService::phoneE164($userProfilePhone->country, $userProfilePhone->phone);
            }

            if ($userProfilePhone->isDirty('status') === UserProfilePhoneStatusEnum::Approved->value) {
                $userProfilePhone->verified_at = now();
            }
        });
    }
}
