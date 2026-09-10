<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Misaf\VendraDelivery\Support\DeliverySchedule;

it('offers today when the same-day cutoff has not passed', function (): void {
    Config::set('vendra-delivery.schedule.same_day_cutoff_hour', 14);

    $dates = (new DeliverySchedule)->bookableDates(Date::parse('2026-09-01 09:30:00'));

    expect(Arr::get($dates, 0))->toBe('2026-09-01');
});

it('starts from tomorrow once the cutoff has passed', function (): void {
    Config::set('vendra-delivery.schedule.same_day_cutoff_hour', 14);

    $dates = (new DeliverySchedule)->bookableDates(Date::parse('2026-09-01 15:00:00'));

    expect(Arr::get($dates, 0))->toBe('2026-09-02');
});

it('offers as many days ahead as configured', function (): void {
    Config::set('vendra-delivery.schedule.advance_days', 5);

    $dates = (new DeliverySchedule)->bookableDates(Date::parse('2026-09-01 09:00:00'));

    expect($dates)->toHaveCount(5)
        ->and(Arr::get($dates, 4))->toBe('2026-09-05');
});

it('rejects a date outside the bookable window', function (): void {
    Config::set('vendra-delivery.schedule.advance_days', 3);

    $schedule = new DeliverySchedule;
    $from = Date::parse('2026-09-01 09:00:00');

    expect($schedule->isBookable('2026-09-02', $from))->toBeTrue()
        ->and($schedule->isBookable('2026-10-20', $from))->toBeFalse();
});
