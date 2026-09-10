<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraDelivery\Database\Factories\DeliveryFactory;
use Misaf\VendraOrder\Models\Order;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * Where and when one order travels, plus the fee that was quoted for it.
 *
 * The fee is a snapshot: re-pricing a zone later never rewrites what a
 * customer was charged, and the order keeps its own `delivery_amount`.
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $order_id
 * @property int|null $address_id
 * @property int|null $delivery_zone_id
 * @property int|null $delivery_slot_id
 * @property Carbon|null $scheduled_for
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $distance_km
 * @property string $currency_code
 * @property Money $fee_amount
 * @property bool $requires_quote
 * @property string|null $recipient_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'order_id',
    'address_id',
    'delivery_zone_id',
    'delivery_slot_id',
    'scheduled_for',
    'latitude',
    'longitude',
    'distance_km',
    'currency_code',
    'fee_amount',
    'requires_quote',
    'recipient_name',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(DeliveryFactory::class)]
final class Delivery extends Model implements ShouldLogActivity
{
    use BelongsToTenant;

    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'order_id' => 'integer',
            'address_id' => 'integer',
            'delivery_zone_id' => 'integer',
            'delivery_slot_id' => 'integer',
            'scheduled_for' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'distance_km' => 'float',
            'currency_code' => 'string',
            'fee_amount' => MoneyIntegerCast::class.':currency_code',
            'requires_quote' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return BelongsTo<DeliveryZone, $this>
     */
    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    /**
     * @return BelongsTo<DeliverySlot, $this>
     */
    public function deliverySlot(): BelongsTo
    {
        return $this->belongsTo(DeliverySlot::class);
    }
}
