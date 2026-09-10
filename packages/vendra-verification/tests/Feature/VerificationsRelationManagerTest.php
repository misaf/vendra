<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Capabilities\Countries;
use Misaf\VendraVerification\Filament\RelationManagers\VerificationsRelationManager;

it('uses a searchable localized country select', function (): void {
    app()->setLocale('fa');

    $relationManager = new VerificationsRelationManager;
    $schema = $relationManager->form(Schema::make($relationManager));
    $field = Arr::get($schema->getFlatFields(), 'country_code');

    expect($field)
        ->toBeInstanceOf(Select::class)
        ->and($field->isSearchable())->toBeTrue()
        ->and($field->getOptions())->toBe(Countries::options());
});
