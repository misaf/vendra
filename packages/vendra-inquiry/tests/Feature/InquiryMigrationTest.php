<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

it('creates the inquiries table', function (): void {
    Schema::dropIfExists('inquiries');

    /** @var Migration $migration */
    $migration = require __DIR__.'/../../database/migrations/create_inquiries_table.php.stub';

    $migration->up();

    expect(Schema::hasColumns('inquiries', [
        'name',
        'email',
        'phone',
        'occasion',
        'message',
        'status',
        'source',
        'locale',
        'metadata',
        'answered_at',
        'deleted_at',
    ]))->toBeTrue();

    $migration->down();

    expect(Schema::hasTable('inquiries'))->toBeFalse();
});
