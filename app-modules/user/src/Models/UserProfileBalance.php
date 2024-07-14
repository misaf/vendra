<?php

declare(strict_types=1);

namespace Termehsoft\User\Models;

use App\Casts\DateCast;
use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\Currency\Contracts\BelongsToCurrency as BelongsToCurrencyInterface;
use Termehsoft\Currency\Contracts\BelongsToCurrencyCategory as BelongsToCurrencyCategoryInterface;
use Termehsoft\Currency\Traits\BelongsToCurrency as BelongsToCurrencyTrait;
use Termehsoft\Currency\Traits\BelongsToCurrencyCategoryThroughCurrency as BelongsToCurrencyCategoryThroughCurrencyTrait;
use Termehsoft\Tenant\Models\Tenant;
use Termehsoft\User\Contracts\BelongsToUser as BelongsToUserInterface;
use Termehsoft\User\Contracts\BelongsToUserProfile as BelongsToUserProfileInterface;
use Termehsoft\User\Traits\BelongsToUserProfile as BelongsToUserProfileTrait;
use Termehsoft\User\Traits\BelongsToUserThroughUserProfile as BelongsToUserThroughUserProfileTrait;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

final class UserProfileBalance extends Tenant implements
    BelongsToUserInterface,
    BelongsToUserProfileInterface,
    BelongsToCurrencyInterface,
    BelongsToCurrencyCategoryInterface
{
    use BelongsToCurrencyCategoryThroughCurrencyTrait;
    use BelongsToCurrencyTrait;
    use BelongsToThroughTrait;
    use BelongsToUserProfileTrait;
    use BelongsToUserThroughUserProfileTrait;
    use SoftDeletes;

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
