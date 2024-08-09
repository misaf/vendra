<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Casts\MoneyCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\Currency;
use Termehsoft\User;
use Termehsoft\User\Observers\UserProfileBalanceObserver;

#[ObservedBy([UserProfileBalanceObserver::class])]
final class UserProfileBalance extends BaseModel implements
    User\Contracts\BelongsToUser,
    User\Contracts\BelongsToUserProfile,
    Currency\Contracts\BelongsToCurrency,
    Currency\Contracts\BelongsToCurrencyCategory
{
    use Currency\Traits\BelongsToCurrency;
    use Currency\Traits\BelongsToCurrencyCategoryThroughCurrency;
    use SoftDeletes;
    use User\Traits\BelongsToUserProfile;
    use User\Traits\BelongsToUserThroughUserProfile;

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
}
