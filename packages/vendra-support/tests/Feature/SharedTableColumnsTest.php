<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Misaf\VendraSupport\Filament\Tables\Columns\CreatedAtColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\RowIndexColumn;
use Misaf\VendraSupport\Filament\Tables\Columns\UpdatedAtColumn;

it('defaults the timestamp and row index columns to their shared names and labels', function (): void {
    expect(CreatedAtColumn::make()->getName())->toBe('created_at')
        ->and(CreatedAtColumn::make()->getLabel())->toBe(__('vendra-support::attributes.created_at'))
        ->and(UpdatedAtColumn::make()->getName())->toBe('updated_at')
        ->and(UpdatedAtColumn::make()->getLabel())->toBe(__('vendra-support::attributes.updated_at'))
        ->and(RowIndexColumn::make()->getName())->toBe('row')
        ->and(RowIndexColumn::make()->getLabel())->toBe('#');
});

it('formats timestamps with the Jalali calendar only for the Persian locale', function (): void {
    app()->setLocale('en');

    expect(CreatedAtColumn::make()->formatState('2026-03-21 10:30:00'))->toBe('2026-03-21 10:30');

    app()->setLocale('fa');

    expect(CreatedAtColumn::make()->formatState('2026-03-21 10:30:00'))->toBe('1405-01-01 10:30');
});
