<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Infolists\Components\IsActiveEntry;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;
use Misaf\VendraSupport\Filament\Tables\Filters\IsActiveFilter;
use Misaf\VendraSupport\Filament\Tables\Filters\QueryBuilder\Constraints\IsActiveConstraint;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Schema::create('support_test_active_records', function (Blueprint $table): void {
        $table->id();
        $table->boolean('active');
        $table->timestamps();
    });
});

it('defaults both controls to the active column with the shared label and icon', function (): void {
    $toggle = IsActiveToggle::make();
    $column = IsActiveToggleColumn::make();

    expect($toggle->getName())->toBe('active')
        ->and($toggle->getOnIcon())->toBe(Heroicon::Bolt)
        ->and($toggle->isRequired())->toBeTrue()
        ->and($column->getName())->toBe('active')
        ->and($column->getLabel())->toBe(__('vendra-support::attributes.active'))
        ->and($column->getOnIcon())->toBe(Heroicon::Bolt)
        ->and(IsActiveToggle::make('in_stock')->getName())->toBe('in_stock');
});

it('validates the toggle as soon as its state changes', function (): void {
    livewire(SupportTestIsActiveToggleComponent::class)
        ->assertSet('data.active', true)
        ->set('data.active', false)
        ->assertHasNoErrors()
        ->set('data.active', null)
        ->assertHasErrors(['data.active' => 'required']);
});

it('flips the record from the table column', function (): void {
    $record = SupportTestActiveRecord::query()->create(['active' => true]);

    livewire(SupportTestIsActiveToggleComponent::class)
        ->call('updateTableColumnState', 'active', (string) $record->getKey(), false);

    expect($record->refresh()->active)->toBeFalse();
});

it('labels the active entry, constraint and filter with the shared translations', function (): void {
    $entry = IsActiveEntry::make();
    $constraint = IsActiveConstraint::make();
    $filter = IsActiveFilter::make();

    expect($entry->getName())->toBe('active')
        ->and($entry->getLabel())->toBe(__('vendra-support::attributes.active'))
        ->and($entry->isBoolean())->toBeTrue()
        ->and($constraint->getName())->toBe('active')
        ->and($constraint->getLabel())->toBe(__('vendra-support::attributes.active'))
        ->and($filter->getName())->toBe('active')
        ->and($filter->getLabel())->toBe(__('vendra-support::attributes.active'))
        ->and($filter->getTrueLabel())->toBe(__('vendra-support::attributes.active'))
        ->and($filter->getFalseLabel())->toBe(__('vendra-support::attributes.inactive'));
});
