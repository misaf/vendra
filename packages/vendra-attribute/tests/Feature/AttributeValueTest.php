<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Tests\Fixtures\AttributableRecord;

beforeEach(function (): void {
    Schema::create('attributable_records', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    makeCurrentTestTenant();
});

it('attaches reusable attribute values to any model', function (): void {
    $attribute = Attribute::factory()->create([
        'name' => 'Weight',
        'unit' => 'kg',
    ]);
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $value = $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => '1.2',
    ]);

    expect($record->attributeValues()->sole()->is($value))->toBeTrue()
        ->and($value->attribute->is($attribute))->toBeTrue()
        ->and($value->attributable->is($record))->toBeTrue();
});

it('rejects duplicate values for the same attribute and attributable', function (): void {
    $attribute = Attribute::factory()->create();
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]);

    expect(fn () => $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]))->toThrow(QueryException::class);
});

it('allows reusing a value after the previous one is soft deleted', function (): void {
    $attribute = Attribute::factory()->create();
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ])->delete();

    $replacement = $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]);

    expect($replacement->exists)->toBeTrue();
});

it('allows the same value on the same attribute for a different attributable', function (): void {
    $attribute = Attribute::factory()->create();
    $first = AttributableRecord::query()->create(['name' => 'First']);
    $second = AttributableRecord::query()->create(['name' => 'Second']);

    $first->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]);

    $duplicateElsewhere = $second->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]);

    expect($duplicateElsewhere->exists)->toBeTrue();
});
