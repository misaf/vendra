<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Casts\DateCast;
use App\Casts\MoneyCast;
use App\Models\Currency;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class UserProfileBalance extends Tenant implements
    Contracts\BelongsToUser,
    Contracts\BelongsToUserProfile,
    Currency\Contracts\BelongsToCurrency,
    Currency\Contracts\BelongsToCurrencyCategory
{
    use Currency\Traits\BelongsToCurrency;
    use Currency\Traits\BelongsToCurrency;
    use Currency\Traits\BelongsToCurrencyCategoryThroughCurrency;
    use SoftDeletes;
    use TraitBelongsToThrough;
    use Traits\BelongsToUserProfile;
    use Traits\BelongsToUserThroughUserProfile;

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
