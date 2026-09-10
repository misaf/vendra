<?php

declare(strict_types=1);

use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;
use Misaf\VendraDelivery\Support\DeliveryZoneMatcher;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

/**
 * The studio on Vali Asr Street, and two pins measured from it.
 */
const STUDIO_LATITUDE = 35.6892;
const STUDIO_LONGITUDE = 51.3890;

it('prices a pin inside the free band at nothing', function (): void {
    DeliveryZoneFactory::new()->freeWithin(12)->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 1,
    ]);

    $quote = resolve(DeliveryZoneMatcher::class)->quoteFor(35.6950, STUDIO_LONGITUDE, 'USD');

    expect($quote->isDeliverable())->toBeTrue()
        ->and($quote->feeAmount)->toBe(0)
        ->and($quote->distanceKm)->toBeLessThan(1.0)
        ->and($quote->zone)->not->toBeNull();
});

it('charges the first band that still covers the pin', function (): void {
    $free = DeliveryZoneFactory::new()->freeWithin(2)->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 1,
    ]);
    $paid = DeliveryZoneFactory::new()->chargingWithin(30, 1500)->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 2,
    ]);

    $quote = resolve(DeliveryZoneMatcher::class)->quoteFor(35.7219, 51.2334, 'USD');

    expect($quote->zone?->getKey())->toBe($paid->getKey())
        ->and($quote->zone?->getKey())->not->toBe($free->getKey())
        ->and($quote->feeAmount)->toBe(1500)
        ->and($quote->isDeliverable())->toBeTrue();
});

it('marks an address beyond every band as quoted by hand', function (): void {
    DeliveryZoneFactory::new()->freeWithin(12)->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 1,
    ]);

    // Isfahan, several hundred kilometres from the studio.
    $quote = resolve(DeliveryZoneMatcher::class)->quoteFor(32.6546, 51.6680, 'USD');

    expect($quote->isDeliverable())->toBeFalse()
        ->and($quote->requiresQuote)->toBeTrue()
        ->and($quote->zone)->toBeNull()
        ->and($quote->feeAmount)->toBe(0);
});

it('uses the outermost quote-by-hand band when one is configured', function (): void {
    DeliveryZoneFactory::new()->freeWithin(12)->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 1,
    ]);
    $outer = DeliveryZoneFactory::new()->quotedByHand()->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 2,
    ]);

    $quote = resolve(DeliveryZoneMatcher::class)->quoteFor(32.6546, 51.6680, 'USD');

    expect($quote->zone?->getKey())->toBe($outer->getKey())
        ->and($quote->requiresQuote)->toBeTrue()
        ->and($quote->isDeliverable())->toBeFalse();
});

it('ignores inactive bands', function (): void {
    DeliveryZoneFactory::new()->freeWithin(12)->inactive()->createOne([
        'origin_latitude' => STUDIO_LATITUDE,
        'origin_longitude' => STUDIO_LONGITUDE,
        'position' => 1,
    ]);

    $quote = resolve(DeliveryZoneMatcher::class)->quoteFor(35.6950, STUDIO_LONGITUDE, 'USD');

    expect($quote->requiresQuote)->toBeTrue()
        ->and($quote->zone)->toBeNull();
});
