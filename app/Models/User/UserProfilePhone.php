<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Support\Enums\UserProfileDocumentStatusEnum;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStatus\HasStatuses;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class UserProfilePhone extends Model
{
    use BelongsToTenant;

    use HasFactory;

    // use HasStatuses;

    use LogsActivity;

    use SoftDeletes;

    use TraitsBelongsToThrough;

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

        static::saving(function (\App\Models\User\UserProfilePhone $userProfilePhone): void {
            if ($userProfilePhone->isDirty('phone') && $userProfilePhone->phone) {
                $userProfilePhone->phone_normalized = \App\Services\UserProfilePhoneService::phoneNormalized($userProfilePhone->phone);
                $userProfilePhone->phone_national = \App\Services\UserProfilePhoneService::phoneNational($userProfilePhone->country, $userProfilePhone->phone);
                $userProfilePhone->phone_e164 = \App\Services\UserProfilePhoneService::phoneE164($userProfilePhone->country, $userProfilePhone->phone);
            }

            if ($userProfilePhone->isDirty('status') === \App\Support\Enums\UserProfilePhoneStatusEnum::Approved->value) {
                $userProfilePhone->verified_at = now();
            }
        });
    }

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
