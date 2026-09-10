<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Misaf\VendraDelivery\Actions\ScheduleDeliveryAction;
use Misaf\VendraDelivery\Data\DeliveryQuote;
use Misaf\VendraDelivery\Database\Factories\DeliverySlotFactory;
use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;
use Misaf\VendraDelivery\Models\Delivery;
use Misaf\VendraOrder\Database\Factories\OrderFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('schedules a delivery against an order with the quoted fee', function (): void {
    $order = OrderFactory::new()->createOne();
    $zone = DeliveryZoneFactory::new()->chargingWithin(30, 1500)->createOne();
    $slot = DeliverySlotFactory::new()->window('Afternoon', '12:00:00', '17:00:00')->createOne();
    $date = now()->addDay()->toDateString();

    $delivery = resolve(ScheduleDeliveryAction::class)->execute(
        order: $order,
        quote: new DeliveryQuote($zone, 4.2, 1500, 'USD', false),
        scheduledFor: $date,
        slot: $slot,
        recipientName: 'Nasrin K.',
        latitude: 35.7219,
        longitude: 51.3347,
    );

    expect($delivery->order_id)->toBe($order->id)
        ->and($delivery->delivery_zone_id)->toBe($zone->id)
        ->and($delivery->delivery_slot_id)->toBe($slot->id)
        ->and($delivery->scheduled_for?->toDateString())->toBe($date)
        ->and($delivery->fee_amount->getAmount())->toBe('1500')
        ->and($delivery->distance_km)->toBe(4.2)
        ->and($delivery->recipient_name)->toBe('Nasrin K.');
});

it('keeps one delivery per order when it is rescheduled', function (): void {
    $order = OrderFactory::new()->createOne();
    $zone = DeliveryZoneFactory::new()->freeWithin(12)->createOne();
    $action = resolve(ScheduleDeliveryAction::class);
    $quote = new DeliveryQuote($zone, 2.0, 0, 'USD', false);

    $first = $action->execute($order, $quote, now()->addDay()->toDateString());
    $second = $action->execute($order, $quote, now()->addDays(2)->toDateString());

    expect(Delivery::query()->count())->toBe(1)
        ->and($second->getKey())->toBe($first->getKey())
        ->and($second->scheduled_for?->toDateString())->toBe(now()->addDays(2)->toDateString());
});

it('refuses to schedule an address that is quoted by hand', function (): void {
    $order = OrderFactory::new()->createOne();

    expect(fn (): Delivery => resolve(ScheduleDeliveryAction::class)->execute(
        order: $order,
        quote: DeliveryQuote::outOfRange(430.0, 'USD'),
    ))->toThrow(RuntimeException::class)
        ->and(Delivery::query()->count())->toBe(0);
});

it('refuses a date outside the bookable window', function (): void {
    Config::set('vendra-delivery.schedule.advance_days', 3);

    $order = OrderFactory::new()->createOne();
    $zone = DeliveryZoneFactory::new()->freeWithin(12)->createOne();

    expect(fn (): Delivery => resolve(ScheduleDeliveryAction::class)->execute(
        order: $order,
        quote: new DeliveryQuote($zone, 2.0, 0, 'USD', false),
        scheduledFor: now()->addMonths(2)->toDateString(),
    ))->toThrow(RuntimeException::class)
        ->and(Delivery::query()->count())->toBe(0);
});

it('reports whether a capped window still has room on a date', function (): void {
    $slot = DeliverySlotFactory::new()->withCapacity(1)->createOne();
    $zone = DeliveryZoneFactory::new()->freeWithin(12)->createOne();
    $date = now()->addDay()->toDateString();

    expect($slot->hasRoomOn($date))->toBeTrue();

    resolve(ScheduleDeliveryAction::class)->execute(
        order: OrderFactory::new()->createOne(),
        quote: new DeliveryQuote($zone, 1.0, 0, 'USD', false),
        scheduledFor: $date,
        slot: $slot,
    );

    expect($slot->hasRoomOn($date))->toBeFalse();
});
