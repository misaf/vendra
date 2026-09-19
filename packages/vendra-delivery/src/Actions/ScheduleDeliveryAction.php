<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraDelivery\Data\DeliveryQuote;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Support\DeliverySchedule;
use Misaf\VendraOrder\Models\Order;
use RuntimeException;

final readonly class ScheduleDeliveryAction
{
    public function __construct(private DeliverySchedule $schedule) {}

    /**
     * The fee is the one checkout charged, and addresses that need a manual
     * quote are refused. Rescheduling updates the order's single delivery row.
     * The caller validates the date format and name.
     */
    public function execute(
        Order $order,
        DeliveryQuote $quote,
        ?string $scheduledFor = null,
        ?DeliverySlot $slot = null,
        ?Address $address = null,
        ?string $recipientName = null,
        ?float $latitude = null,
        ?float $longitude = null,
    ): Delivery {
        throw_unless($quote->isDeliverable(), RuntimeException::class, 'The delivery address is outside the delivered range and must be quoted by hand.');

        if ($scheduledFor !== null && ! $this->schedule->isBookable($scheduledFor)) {
            throw new RuntimeException(sprintf('Delivery date [%s] is not bookable.', $scheduledFor));
        }

        return DB::transaction(function () use (
            $order,
            $quote,
            $scheduledFor,
            $slot,
            $address,
            $recipientName,
            $latitude,
            $longitude,
        ): Delivery {
            $delivery = Delivery::query()
                ->where('order_id', $order->getKey())
                ->lockForUpdate()
                ->first() ?? new Delivery;

            $delivery->fill([
                'order_id' => $order->getKey(),
                'address_id' => $address?->getKey(),
                'delivery_zone_id' => $quote->zone?->getKey(),
                'delivery_slot_id' => $slot?->getKey(),
                'scheduled_for' => $scheduledFor,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'distance_km' => $quote->distanceKm,
                'currency_code' => $quote->currencyCode,
                'fee_amount' => $quote->feeAmount,
                'requires_quote' => false,
                'recipient_name' => $recipientName,
            ]);

            $delivery->save();

            return $delivery;
        }, attempts: 3);
    }
}
