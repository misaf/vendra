<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Misaf\VendraCart\Models\Cart;
use Misaf\VendraOrder\Database\Factories\OrderFactory;
use Misaf\VendraOrder\States\Cancelled;
use Misaf\VendraOrder\States\Completed;
use Misaf\VendraOrder\States\Confirmed;
use Misaf\VendraOrder\States\OrderState;
use Misaf\VendraOrder\States\Pending;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Spatie\ModelStates\HasStates;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string|null $customer_type
 * @property int|null $customer_id
 * @property-read string|null $customer_label
 * @property int|null $cart_id
 * @property int|null $transaction_gateway_id
 * @property string $number
 * @property OrderState $status
 * @property string $currency_code
 * @property Money $items_amount
 * @property Money $delivery_amount
 * @property Money $total_amount
 * @property string|null $payment_reference
 * @property string|null $card_message
 * @property Carbon|null $placed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'customer_type',
    'customer_id',
    'cart_id',
    'transaction_gateway_id',
    'number',
    'status',
    'currency_code',
    'items_amount',
    'delivery_amount',
    'total_amount',
    'payment_reference',
    'card_message',
    'placed_at',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(OrderFactory::class)]
final class Order extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use HasStates;
    use SoftDeletes;

    /**
     * Default the customer-facing number so an order is never persisted
     * without the reference customers quote back to support.
     */
    protected static function booted(): void
    {
        self::creating(function (self $order): void {
            if (array_key_exists('number', $order->getAttributes())) {
                return;
            }

            $order->number = self::generateNumber();
        });
    }

    public static function generateNumber(): string
    {
        return Str::upper(Config::string('vendra-order.number_prefix', 'ORD').'-'.Str::random(10));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'customer_id' => 'integer',
            'cart_id' => 'integer',
            'transaction_gateway_id' => 'integer',
            'number' => 'string',
            'status' => OrderState::class,
            'currency_code' => 'string',
            'items_amount' => MoneyIntegerCast::class.':currency_code',
            'delivery_amount' => MoneyIntegerCast::class.':currency_code',
            'total_amount' => MoneyIntegerCast::class.':currency_code',
            'placed_at' => 'datetime',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function customer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * @return BelongsTo<TransactionGateway, $this>
     */
    public function transactionGateway(): BelongsTo
    {
        return $this->belongsTo(TransactionGateway::class);
    }

    /**
     * @return HasMany<OrderLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function confirm(): void
    {
        $this->status->transitionTo(Confirmed::class);
    }

    public function complete(): void
    {
        $this->status->transitionTo(Completed::class);
    }

    public function cancel(): void
    {
        $this->status->transitionTo(Cancelled::class);
    }

    /**
     * @param Builder<self> $builder
     */
    #[Scope]
    protected function pending(Builder $builder): void
    {
        $builder->whereState('status', Pending::class);
    }

    /**
     * @param Builder<self> $builder
     */
    #[Scope]
    protected function confirmed(Builder $builder): void
    {
        $builder->whereState('status', Confirmed::class);
    }

    /**
     * @param Builder<self> $builder
     */
    #[Scope]
    protected function completed(Builder $builder): void
    {
        $builder->whereState('status', Completed::class);
    }

    /**
     * @param Builder<self> $builder
     */
    #[Scope]
    protected function cancelled(Builder $builder): void
    {
        $builder->whereState('status', Cancelled::class);
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function customerLabel(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $customer = $this->customer;

                if (! $customer instanceof Model) {
                    return null;
                }

                foreach (['username', 'name', 'email'] as $attribute) {
                    $value = $customer->getAttribute($attribute);

                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }

                $routeKey = $customer->getRouteKey();

                return is_int($routeKey) || is_string($routeKey)
                    ? (string) $routeKey
                    : null;
            },
        );
    }
}
