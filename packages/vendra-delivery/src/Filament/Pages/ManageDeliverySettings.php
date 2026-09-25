<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraDelivery\Settings\DeliverySettings;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;

final class ManageDeliverySettings extends SystemSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = NavigationPriority::DeliverySettings->value;

    protected static string $settings = DeliverySettings::class;

    protected static ?string $slug = 'delivery-settings';

    public static function getNavigationLabel(): string
    {
        return __('vendra-delivery::navigation.delivery_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-delivery::attributes.delivery_settings'))
                    ->description(__('vendra-delivery::attributes.delivery_settings_description'))
                    ->schema([
                        TextInput::make('advance_days')
                            ->label(__('vendra-delivery::attributes.advance_days'))
                            ->helperText(__('vendra-delivery::attributes.advance_days_hint'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(90)
                            ->required(),

                        TextInput::make('same_day_cutoff_hour')
                            ->label(__('vendra-delivery::attributes.same_day_cutoff_hour'))
                            ->helperText(__('vendra-delivery::attributes.same_day_cutoff_hour_hint'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(24)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
