<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttribute\Models\AttributeValue;
use Misaf\VendraAttribute\Models\AttributeValueSelection;
use Misaf\VendraAttribute\Tests\Fixtures\AttributableRecord;

beforeEach(function (): void {
    Schema::create('attributable_records', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    makeCurrentTestTenant();
});

it('creates a selection linking an attribute value to any selectable model', function (): void {
    $attribute = Attribute::factory()->create(['name' => 'Color']);
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $value = AttributeValue::factory()->forAttributable($record)->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]);

    $selection = AttributeValueSelection::query()->create([
        'attribute_value_id' => $value->id,
        'selectable_type' => $record->getMorphClass(),
        'selectable_id' => $record->id,
    ]);

    expect($selection->attributeValue->is($value))->toBeTrue()
        ->and($selection->selectable->is($record))->toBeTrue();
});

it('enforces unique attribute-value/selectable pairs', function (): void {
    $attribute = Attribute::factory()->create(['name' => 'Size']);
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $value = AttributeValue::factory()->forAttributable($record)->create([
        'attribute_id' => $attribute->id,
        'value' => 'Large',
    ]);

    AttributeValueSelection::query()->create([
        'attribute_value_id' => $value->id,
        'selectable_type' => $record->getMorphClass(),
        'selectable_id' => $record->id,
    ]);

    expect(fn () => AttributeValueSelection::query()->create([
        'attribute_value_id' => $value->id,
        'selectable_type' => $record->getMorphClass(),
        'selectable_id' => $record->id,
    ]))->toThrow(QueryException::class);
});

it('can query selections from the attribute value side', function (): void {
    $attribute = Attribute::factory()->create(['name' => 'Material']);
    $first = AttributableRecord::query()->create(['name' => 'First']);
    $second = AttributableRecord::query()->create(['name' => 'Second']);

    $value = AttributeValue::factory()->forAttributable($first)->create([
        'attribute_id' => $attribute->id,
        'value' => 'Wood',
    ]);

    $value->selections()->create([
        'selectable_type' => $first->getMorphClass(),
        'selectable_id' => $first->id,
    ]);

    $value->selections()->create([
        'selectable_type' => $second->getMorphClass(),
        'selectable_id' => $second->id,
    ]);

    expect($value->selections()->count())->toBe(2);
});

it('removes selections when the attribute value is soft deleted', function (): void {
    $attribute = Attribute::factory()->create(['name' => 'Length']);
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $value = AttributeValue::factory()->forAttributable($record)->create([
        'attribute_id' => $attribute->id,
        'value' => '30',
    ]);

    $value->selections()->create([
        'selectable_type' => $record->getMorphClass(),
        'selectable_id' => $record->id,
    ]);

    $value->delete();

    expect($value->fresh()->trashed())->toBeTrue()
        ->and(AttributeValueSelection::query()->where('attribute_value_id', $value->id)->exists())->toBeFalse();
});

it('does not resurrect selections when a soft deleted attribute value is restored', function (): void {
    $attribute = Attribute::factory()->create(['name' => 'Width']);
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $value = AttributeValue::factory()->forAttributable($record)->create([
        'attribute_id' => $attribute->id,
        'value' => '20',
    ]);

    $value->selections()->create([
        'selectable_type' => $record->getMorphClass(),
        'selectable_id' => $record->id,
    ]);

    $value->delete();
    $value->restore();

    expect($value->fresh()->trashed())->toBeFalse()
        ->and(AttributeValueSelection::query()->where('attribute_value_id', $value->id)->exists())->toBeFalse();
});

it('cascades selections when the attribute value is deleted', function (): void {
    $attribute = Attribute::factory()->create(['name' => 'Weight']);
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $value = AttributeValue::factory()->forAttributable($record)->create([
        'attribute_id' => $attribute->id,
        'value' => '1.5',
    ]);

    $value->selections()->create([
        'selectable_type' => $record->getMorphClass(),
        'selectable_id' => $record->id,
    ]);

    $value->forceDelete();

    expect(AttributeValueSelection::query()->where('attribute_value_id', $value->id)->exists())->toBeFalse();
});
