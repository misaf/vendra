<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tests\Feature;

use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraSupport\Filament\Forms\Components\IsActiveToggle;
use Misaf\VendraSupport\Filament\Tables\Columns\IsActiveToggleColumn;

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
