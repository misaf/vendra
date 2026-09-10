<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

it('creates carts and cart items without catalog tables', function (): void {
    Schema::dropIfExists('cart_items');
    Schema::dropIfExists('carts');

    /** @var Migration $migration */
    $migration = require __DIR__.'/../../database/migrations/create_carts_table.php.stub';

    $migration->up();

    expect(Schema::hasColumns('carts', [
        'owner_type',
        'owner_id',
        'token',
        'expires_at',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('cart_items', [
            'cart_id',
            'sellable_type',
            'sellable_id',
            'quantity',
            'metadata',
        ]))->toBeTrue();

    $migration->down();

    expect(Schema::hasTable('cart_items'))->toBeFalse()
        ->and(Schema::hasTable('carts'))->toBeFalse();
});
