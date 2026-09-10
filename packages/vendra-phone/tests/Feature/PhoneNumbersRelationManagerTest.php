<?php

declare(strict_types=1);

use Awcodes\BadgeableColumn\Components\BadgeableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Misaf\VendraPhone\Database\Factories\PhoneNumberFactory;
use Misaf\VendraPhone\Filament\RelationManagers\PhoneNumbersRelationManager;

it('provides a notes field', function (): void {
    $relationManager = new PhoneNumbersRelationManager;
    $schema = $relationManager->form(Schema::make($relationManager));
    $field = Arr::get($schema->getFlatFields(), 'notes');

    expect($field)
        ->toBeInstanceOf(Textarea::class)
        ->and($field->getColumnSpan())->toBe(['default' => 'full']);
});

it('updates verification state from table toggle', function (): void {
    makeCurrentTestTenant();

    $relationManager = new PhoneNumbersRelationManager;
    $table = $relationManager->table(Table::make($relationManager));
    $phoneNumber = PhoneNumberFactory::new()->createOne();
    $verifiedColumn = $table->getColumn('verified_at');

    expect($verifiedColumn)->toBeInstanceOf(ToggleColumn::class);

    $verifiedColumn->record($phoneNumber)->updateState(true);

    expect($phoneNumber->refresh()->verified_at)->not->toBeNull();

    $verifiedColumn->record($phoneNumber)->updateState(false);

    expect($phoneNumber->refresh()->verified_at)->toBeNull();
});

it('shows primary badge on label column for primary phone numbers', function (): void {
    makeCurrentTestTenant();

    $relationManager = new PhoneNumbersRelationManager;
    $table = $relationManager->table(Table::make($relationManager));
    $phoneNumber = PhoneNumberFactory::new()->createOne(['is_primary' => true]);
    $labelColumn = $table->getColumn('label');

    expect($labelColumn)->toBeInstanceOf(BadgeableColumn::class);

    $state = $labelColumn->record($phoneNumber)->formatState($phoneNumber->label)->toHtml();

    expect($state)->toContain('badgeable-column-badge');
});
