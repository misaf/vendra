<?php

declare(strict_types=1);

namespace App\Models\Order;

use App\Casts\DateCast;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\Transaction\Transaction;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

#[ScopedBy(TenantScope::class)]
final class Order extends Model
{
    use BelongsToTenant;

    use HasFactory;

    use SoftDeletes;

    use TraitBelongsToThrough;

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
        return $this->belongsTo(Currency::class);
    }

    public function currencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(CurrencyCategory::class, Currency::class);
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
