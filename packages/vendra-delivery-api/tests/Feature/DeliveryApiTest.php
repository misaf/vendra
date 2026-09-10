<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Misaf\VendraDelivery\Database\Factories\DeliverySlotFactory;
use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('lists only active delivery bands, tightest first', function (): void {
    $free = DeliveryZoneFactory::new()->freeWithin(12)->createOne(['name' => ['en' => 'Free zone'], 'position' => 1]);
    $paid = DeliveryZoneFactory::new()->chargingWithin(30, 1500)->createOne(['name' => ['en' => 'Outside'], 'position' => 2]);
    $hidden = DeliveryZoneFactory::new()->inactive()->createOne(['position' => 3]);

    $this->getJson('/api/delivery/zones', ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('totalItems', 2)
        ->assertJsonPath('member.0.id', $free->id)
        ->assertJsonPath('member.0.feeAmount', 0)
        ->assertJsonPath('member.1.id', $paid->id)
        ->assertJsonPath('member.1.feeAmount', 1500)
        ->assertJsonMissing(['id' => $hidden->id]);
});

it('lists bookable dates and active delivery windows', function (): void {
    Config::set('vendra-delivery.schedule.advance_days', 3);

    $morning = DeliverySlotFactory::new()->window('Morning', '09:00:00', '12:00:00')->createOne(['position' => 1]);
    DeliverySlotFactory::new()->window('Night', '20:00:00', '23:00:00')->inactive()->createOne(['position' => 2]);

    $response = $this->getJson('/api/delivery/schedule', ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('slots.0.id', $morning->id)
        ->assertJsonPath('slots.0.startsAt', '09:00:00');

    expect($response->json('dates'))->toHaveCount(3)
        ->and($response->json('slots'))->toHaveCount(1);
});

it('prices a pin inside a charged band', function (): void {
    DeliveryZoneFactory::new()->freeWithin(2)->createOne([
        'name' => ['en' => 'Free zone'],
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
        'position' => 1,
    ]);
    $paid = DeliveryZoneFactory::new()->chargingWithin(30, 1500)->createOne([
        'name' => ['en' => 'Outside the free zone'],
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
        'position' => 2,
    ]);

    $this->postJson('/api/delivery/quotes', [
        'latitude' => 35.7219,
        'longitude' => 51.2334,
        'currencyCode' => 'USD',
    ])
        ->assertOk()
        ->assertJsonPath('zoneId', $paid->id)
        ->assertJsonPath('feeAmount', 1500)
        ->assertJsonPath('currencyCode', 'USD')
        ->assertJsonPath('requiresQuote', false);
});

it('reports an address beyond every band as quoted by hand', function (): void {
    DeliveryZoneFactory::new()->freeWithin(12)->createOne([
        'origin_latitude' => 35.6892,
        'origin_longitude' => 51.3890,
        'position' => 1,
    ]);

    $this->postJson('/api/delivery/quotes', ['latitude' => 32.6546, 'longitude' => 51.6680])
        ->assertOk()
        ->assertJsonPath('requiresQuote', true)
        ->assertJsonPath('feeAmount', 0);
});

it('validates the dropped pin', function (): void {
    $this->postJson('/api/delivery/quotes', ['latitude' => 120, 'longitude' => 51.3890])
        ->assertUnprocessable();

    $this->postJson('/api/delivery/quotes', ['longitude' => 51.3890])
        ->assertUnprocessable();
});
