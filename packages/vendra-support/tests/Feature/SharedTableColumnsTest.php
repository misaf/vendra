<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraSupport\Filament\Infolists\Components\CreatedAtEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\DateTimeEntry;
use Misaf\VendraSupport\Filament\Infolists\Components\UpdatedAtEntry;
use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DeletedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\DescriptionColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsDefaultIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\IsPrimaryIconColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\NameColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\IsDefaultFilter;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\NameConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\PositionConstraint;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\SlugConstraint;

it('defaults the timestamp and row index columns to their shared names and labels', function (): void {
    expect(CreatedAtColumn::make()->getName())->toBe('created_at')
        ->and(CreatedAtColumn::make()->getLabel())->toBe(__('vendra-support::attributes.created_at'))
        ->and(UpdatedAtColumn::make()->getName())->toBe('updated_at')
        ->and(UpdatedAtColumn::make()->getLabel())->toBe(__('vendra-support::attributes.updated_at'))
        ->and(DeletedAtColumn::make()->getName())->toBe('deleted_at')
        ->and(DeletedAtColumn::make()->getLabel())->toBe(__('vendra-support::attributes.deleted_at'))
        ->and(DeletedAtColumn::make()->getPlaceholder())->toBe('—')
        ->and(RowIndexColumn::make()->getName())->toBe('row')
        ->and(RowIndexColumn::make()->getLabel())->toBe('#');
});

it('formats timestamps with the Jalali calendar only for the Persian locale', function (): void {
    app()->setLocale('en');

    expect(CreatedAtColumn::make()->formatState('2026-03-21 10:30:00'))->toBe('2026-03-21 10:30');

    app()->setLocale('fa');

    expect(CreatedAtColumn::make()->formatState('2026-03-21 10:30:00'))->toBe('1405-01-01 10:30');
});

it('defaults the timestamp entries to their shared names and labels', function (): void {
    expect(CreatedAtEntry::make()->getName())->toBe('created_at')
        ->and(CreatedAtEntry::make()->getLabel())->toBe(__('vendra-support::attributes.created_at'))
        ->and(UpdatedAtEntry::make()->getName())->toBe('updated_at')
        ->and(UpdatedAtEntry::make()->getLabel())->toBe(__('vendra-support::attributes.updated_at'));
});

it('formats timestamp entries with the Jalali calendar only for the Persian locale', function (): void {
    app()->setLocale('en');

    expect(DateTimeEntry::make('sent_at')->container(Schema::make())->formatState('2026-03-21 10:30:00'))->toBe('2026-03-21 10:30');

    app()->setLocale('fa');

    expect(DateTimeEntry::make('sent_at')->container(Schema::make())->formatState('2026-03-21 10:30:00'))->toBe('1405-01-01 10:30');
});

it('defaults the position, name and slug constraints and the active icon column to their shared labels', function (): void {
    expect(PositionConstraint::make()->getName())->toBe('position')
        ->and(PositionConstraint::make()->getLabel())->toBe(__('vendra-support::attributes.position'))
        ->and(NameConstraint::make()->getName())->toBe('name')
        ->and(NameConstraint::make()->getLabel())->toBe(__('vendra-support::attributes.name'))
        ->and(SlugConstraint::make()->getName())->toBe('slug')
        ->and(SlugConstraint::make()->getLabel())->toBe(__('vendra-support::attributes.slug'))
        ->and(IsActiveIconColumn::make()->getName())->toBe('active')
        ->and(IsActiveIconColumn::make()->getLabel())->toBe(__('vendra-support::attributes.active'))
        ->and(IsActiveIconColumn::make()->isBoolean())->toBeTrue();
});

it('defaults the default and primary icon columns and the default filter to their shared names and labels', function (): void {
    expect(IsDefaultIconColumn::make()->getName())->toBe('is_default')
        ->and(IsDefaultIconColumn::make()->getLabel())->toBe(__('vendra-support::attributes.is_default'))
        ->and(IsDefaultIconColumn::make()->isBoolean())->toBeTrue()
        ->and(IsPrimaryIconColumn::make()->getName())->toBe('is_primary')
        ->and(IsPrimaryIconColumn::make()->getLabel())->toBe(__('vendra-support::attributes.is_primary'))
        ->and(IsPrimaryIconColumn::make()->isBoolean())->toBeTrue()
        ->and(IsDefaultFilter::make()->getName())->toBe('is_default')
        ->and(IsDefaultFilter::make()->getLabel())->toBe(__('vendra-support::attributes.is_default'));
});

it('defaults the name and description columns to their shared labels and icons', function (): void {
    $nameColumn = NameColumn::make();
    $descriptionColumn = DescriptionColumn::make();

    expect($nameColumn->getName())->toBe('name')
        ->and($nameColumn->getLabel())->toBe(__('vendra-support::attributes.name'))
        ->and($nameColumn->getIcon(null))->toBe(Heroicon::Tag)
        ->and($descriptionColumn->getName())->toBe('description')
        ->and($descriptionColumn->getLabel())->toBe(__('vendra-support::attributes.description'))
        ->and($descriptionColumn->getIcon(null))->toBe(Heroicon::DocumentText)
        ->and($descriptionColumn->isToggledHiddenByDefault())->toBeTrue()
        ->and($descriptionColumn->getCharacterLimit())->toBe(50);
});
