<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

it('creates delivery zones, windows and deliveries', function (): void {
    Schema::dropIfExists('deliveries');
    Schema::dropIfExists('delivery_slots');
    Schema::dropIfExists('delivery_zones');

    /** @var Migration $migration */
    $migration = require __DIR__.'/../../database/migrations/create_deliveries_table.php.stub';

    $migration->up();

    expect(Schema::hasColumns('delivery_zones', [
        'name',
        'origin_latitude',
        'origin_longitude',
        'max_distance_km',
        'currency_code',
        'fee_amount',
        'requires_quote',
        'position',
        'active',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('delivery_slots', ['name', 'starts_at', 'ends_at', 'capacity', 'position', 'active']))->toBeTrue()
        ->and(Schema::hasColumns('deliveries', [
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
        ]))->toBeTrue()
        ->and(Schema::hasForeignKey('deliveries', ['order_id']))->toBeTrue();

    $migration->down();

    expect(Schema::hasTable('deliveries'))->toBeFalse()
        ->and(Schema::hasTable('delivery_slots'))->toBeFalse()
        ->and(Schema::hasTable('delivery_zones'))->toBeFalse();
});
