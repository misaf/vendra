<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

it('creates orders and order lines with immutable snapshot columns', function (): void {
    Schema::dropIfExists('order_lines');
    Schema::dropIfExists('orders');

    /** @var Migration $migration */
    $migration = require __DIR__.'/../../database/migrations/create_orders_table.php.stub';

    $migration->up();

    expect(Schema::hasColumns('orders', [
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
    ]))->toBeTrue()
        ->and(Schema::hasColumns('order_lines', [
            'order_id',
            'sellable_type',
            'sellable_id',
            'name',
            'currency_code',
            'quantity',
            'unit_amount',
            'line_amount',
            'metadata',
        ]))->toBeTrue()
        ->and(Schema::hasForeignKey('order_lines', ['order_id']))->toBeTrue();

    $migration->down();

    expect(Schema::hasTable('order_lines'))->toBeFalse()
        ->and(Schema::hasTable('orders'))->toBeFalse();
});
