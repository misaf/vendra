<?php

declare(strict_types=1);

namespace App\Models\Order;

use App\Casts\DateCast;
use App\Models\Currency;
use App\Models\Tenant;
use App\Models\Transaction\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class Order extends Tenant implements
    Currency\Contracts\BelongsToCurrency,
    Currency\Contracts\BelongsToCurrencyCategoryThroughCurrency,
    User\Contracts\BelongsToUser
{
    use Currency\Traits\BelongsToCurrency;

    use Currency\Traits\BelongsToCurrencyCategoryThroughCurrency;

    use SoftDeletes;

    use TraitBelongsToThrough;

    use User\Traits\BelongsToUser;

    protected $casts = [
        'id'              => 'integer',
        'user_id'         => 'integer',
        'currency_id'     => 'integer',
        'description'     => 'string',
        'discount_amount' => 'integer',
        'reference_code'  => 'string',
        'status'          => 'boolean',
        'created_at'      => DateCast::class,
        'updated_at'      => DateCast::class,
        'deleted_at'      => DateCast::class,
    ];

    protected $fillable = [
        'user_id',
        'currency_id',
        'description',
        'discount_amount',
        'reference_code',
        'status',
    ];

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
