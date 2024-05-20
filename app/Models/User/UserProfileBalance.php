<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Casts\MoneyCast;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\ModelStatus\HasStatuses;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

final class UserProfileBalance extends Model
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
        'currency_id'      => 'integer',
        'amount'           => MoneyCast::class,
        'status'           => 'boolean',
        'created_at'       => DateCast::class,
        'updated_at'       => DateCast::class,
        'deleted_at'       => DateCast::class,
    ];

    protected $fillable = [
        'user_profile_id',
        'currency_id',
        'amount',
        'status',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Currency\Currency::class,
        );
    }

    public function currencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Currency\CurrencyCategory::class,
            through: \App\Models\Currency\Currency::class,
        );
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
