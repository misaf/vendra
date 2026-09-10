<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;

it('exposes a configurable, toggleable prune schedule', function (): void {
    expect(config('vendra-cart.schedule.enabled'))->toBeBool()
        ->and(config('vendra-cart.schedule.cron'))->toBeString();
});

it('registers the prune command from the package', function (): void {
    $registered = collect(app(Schedule::class)->events())
        ->contains(fn ($event): bool => str_contains((string) $event->command, 'vendra-cart:prune-expired'));

    expect($registered)->toBeTrue();
});
