<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

it('creates wishlists and their items without catalog tables', function (): void {
    Schema::dropIfExists('wishlist_items');
    Schema::dropIfExists('wishlists');

    /** @var Migration $migration */
    $migration = require __DIR__.'/../../database/migrations/create_wishlists_table.php.stub';

    $migration->up();

    expect(Schema::hasColumns('wishlists', ['owner_type', 'owner_id', 'token', 'name', 'is_default']))->toBeTrue()
        ->and(Schema::hasColumns('wishlist_items', ['wishlist_id', 'sellable_type', 'sellable_id', 'metadata']))->toBeTrue()
        ->and(Schema::hasForeignKey('wishlist_items', ['wishlist_id']))->toBeTrue();

    $migration->down();

    expect(Schema::hasTable('wishlist_items'))->toBeFalse()
        ->and(Schema::hasTable('wishlists'))->toBeFalse();
});
