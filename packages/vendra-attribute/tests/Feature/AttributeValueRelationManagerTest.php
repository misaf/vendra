<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraAttribute\Database\Factories\AttributeFactory;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\Pages\EditAttribute;
use Misaf\VendraAttribute\Filament\Clusters\Resources\Attributes\RelationManagers\AttributeValueRelationManager;
use Misaf\VendraAttribute\Tests\Fixtures\AttributableRecord;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Schema::create('attributable_records', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    setUpFilamentAdminTestContext();
});

it('manages attached attribute values without offering unattached creation', function (): void {
    $attribute = AttributeFactory::new()->createOne();
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $attributeValue = $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => '1.2',
    ]);

    livewire(AttributeValueRelationManager::class, [
        'ownerRecord' => $attribute,
        'pageClass' => EditAttribute::class,
    ])
        ->assertOk()
        ->call('loadTable')
        ->assertCanSeeTableRecords([$attributeValue])
        ->assertActionDoesNotExist('create')
        ->callAction(TestAction::make('edit')->table($attributeValue), [
            'value' => '2.4',
        ])
        ->assertHasNoFormErrors();

    expect($attributeValue->refresh()->value)->toBe('2.4');
});

it('rejects editing a value into a duplicate of a sibling value', function (): void {
    $attribute = AttributeFactory::new()->createOne();
    $record = AttributableRecord::query()->create(['name' => 'Example']);

    $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Red',
    ]);

    $attributeValue = $record->attributeValues()->create([
        'attribute_id' => $attribute->id,
        'value' => 'Blue',
    ]);

    livewire(AttributeValueRelationManager::class, [
        'ownerRecord' => $attribute,
        'pageClass' => EditAttribute::class,
    ])
        ->call('loadTable')
        ->callAction(TestAction::make('edit')->table($attributeValue), [
            'value' => 'Red',
        ])
        ->assertHasFormErrors(['value']);

    expect($attributeValue->refresh()->value)->toBe('Blue');
});
