<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Misaf\VendraDocument\Database\Factories\DocumentFactory;
use Misaf\VendraDocument\Filament\RelationManagers\DocumentsRelationManager;
use Misaf\VendraSupport\Capabilities\Countries;

it('uses a searchable localized country select for the issuing country', function (): void {
    app()->setLocale('fa');

    $relationManager = new DocumentsRelationManager;
    $schema = $relationManager->form(Schema::make($relationManager));
    $field = Arr::get($schema->getFlatFields(), 'issuing_country_code');

    expect($field)
        ->toBeInstanceOf(Select::class)
        ->and($field->isSearchable())->toBeTrue()
        ->and($field->getOptions())->toBe(Countries::options());
});

it('updates verification state from the table toggle', function (): void {
    makeCurrentTestTenant();

    $relationManager = new DocumentsRelationManager;
    $table = $relationManager->table(Table::make($relationManager));
    $document = DocumentFactory::new()->createOne();
    $verifiedColumn = $table->getColumn('verified_at');

    expect($verifiedColumn)->toBeInstanceOf(ToggleColumn::class);

    $verifiedColumn->record($document)->updateState(true);

    expect($document->refresh()->verified_at)->not->toBeNull();

    $verifiedColumn->record($document)->updateState(false);

    expect($document->refresh()->verified_at)->toBeNull();
});
