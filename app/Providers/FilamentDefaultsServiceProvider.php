<?php

declare(strict_types=1);

namespace App\Providers;

use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\ServiceProvider;

/**
 * Cross-panel Filament defaults.
 *
 * These apply to all three panels, so they are configured once here rather than
 * repeated in each panel provider — and they are UI concerns, so they do not
 * belong in {@see AppServiceProvider}, whose job is composition and domain wiring.
 */
final class FilamentDefaultsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Table::configureUsing(fn (Table $table): Table => $table
            ->paginationPageOptions([10, 25, 50])
            ->deferLoading()
            ->defaultNumberLocale('en'));

        DateTimePicker::configureUsing(fn (DateTimePicker $dateTimePicker): DateTimePicker => $dateTimePicker
            ->firstDayOfWeek(6)
            ->when(
                app()->isLocale('fa'),
                fn (DateTimePicker $component): DateTimePicker => $component
                    ->jalali()
                    ->viewData(fn (DateTimePicker $component): array => [
                        'defaultFocusedDate' => $component->getDefaultFocusedDate(),
                    ]),
            )
            ->native(false));

        DatePicker::configureUsing(fn (DatePicker $datePicker): DatePicker => $datePicker
            ->closeOnDateSelection()
            ->displayFormat('Y-m-d'));

        PanelSwitch::configureUsing(fn (PanelSwitch $panelSwitch): PanelSwitch => $panelSwitch
            ->simple()
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER));
    }
}
