<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraDelivery\Data\DeliveryQuote;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraDelivery\Models\DeliverySlot;
use Misaf\VendraDelivery\Support\DeliverySchedule;
use Misaf\VendraOrder\Models\Order;
use RuntimeException;

final class ScheduleDeliveryAction
{
    public function __construct(private readonly DeliverySchedule $schedule) {}

    /**
     * Record where and when a placed order travels.
     *
     * The quote arrives already priced from `DeliveryZoneMatcher`, so the fee
     * written here is the same one checkout charged. An address the studio
     * prices by hand cannot be scheduled: refusing it is the whole point of
     * the outer band.
     *
     * An order has exactly one delivery, so rescheduling updates the existing
     * row rather than adding a second one; the lookup and the write share a
     * transaction to keep two concurrent reschedules from both inserting.
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
        if (! $quote->isDeliverable()) {
            throw new RuntimeException('The delivery address is outside the delivered range and must be quoted by hand.');
        }

        Validator::make([
            'scheduled_for' => $scheduledFor,
            'recipient_name' => $recipientName,
        ], [
            'scheduled_for' => ['nullable', 'date_format:Y-m-d'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
        ])->validate();

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
