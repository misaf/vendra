<?php

declare(strict_types=1);

namespace App\Models\Order;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class Order extends Model
{
    use BelongsToTenant;

    use HasFactory;

    use SoftDeletes;

    use TraitsBelongsToThrough;

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

    public function transactions(): MorphMany
    {
        return $this->morphMany(
            related: \App\Models\Transaction\Transaction::class,
            name: 'transactionable',
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\User::class,
        );
    }
}
