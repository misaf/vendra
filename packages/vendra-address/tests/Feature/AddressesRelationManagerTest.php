<?php

declare(strict_types=1);

use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Misaf\VendraAddress\Database\Factories\AddressFactory;
use Misaf\VendraAddress\Filament\RelationManagers\AddressesRelationManager;
use Misaf\VendraSupport\Capabilities\Countries;

it('uses a localized country select and free-text administrative fields', function (): void {
    app()->setLocale('fa');

    $relationManager = new AddressesRelationManager;
    $schema = $relationManager->form(Schema::make($relationManager));
    $fields = $schema->getFlatFields();

    expect(Arr::get($fields, 'country_code'))
        ->toBeInstanceOf(Select::class)
        ->and(Arr::get($fields, 'country_code')->isSearchable())->toBeTrue()
        ->and(Arr::get($fields, 'country_code')->getOptions())->toBe(Countries::options())
        ->and(Arr::get($fields, 'administrative_area'))->toBeInstanceOf(TextInput::class)
        ->and(Arr::get($fields, 'locality'))->toBeInstanceOf(TextInput::class)
        ->and(Arr::get($fields, 'notes'))->toBeInstanceOf(Textarea::class)
        ->and(Arr::get($fields, 'notes')->getColumnSpan())->toBe(['default' => 'full']);
});

it('updates verification state from table toggle', function (): void {
    makeCurrentTestTenant();

    $relationManager = new AddressesRelationManager;
    $table = $relationManager->table(Table::make($relationManager));
    $address = AddressFactory::new()->createOne();
    $verifiedColumn = $table->getColumn('verified_at');

    expect($verifiedColumn)->toBeInstanceOf(ToggleColumn::class);

    $verifiedColumn->record($address)->updateState(true);

    expect($address->refresh()->verified_at)->not->toBeNull();

    $verifiedColumn->record($address)->updateState(false);

    expect($address->refresh()->verified_at)->toBeNull();
});

it('shows primary badge on label column for primary addresses', function (): void {
    makeCurrentTestTenant();

    $relationManager = new AddressesRelationManager;
    $table = $relationManager->table(Table::make($relationManager));
    $address = AddressFactory::new()->createOne(['is_primary' => true]);
    $labelColumn = $table->getColumn('label');

    expect($labelColumn)->toBeInstanceOf(BadgeableColumn::class);

    $state = $labelColumn->record($address)->formatState($address->label)->toHtml();

    expect($state)->toContain('badgeable-column-badge');
});
