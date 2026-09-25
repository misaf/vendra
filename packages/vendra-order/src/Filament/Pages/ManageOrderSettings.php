<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraOrder\Settings\OrderSettings;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;

final class ManageOrderSettings extends SystemSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = NavigationPriority::OrderSettings->value;

    protected static string $settings = OrderSettings::class;

    protected static ?string $slug = 'order-settings';

    public static function getNavigationLabel(): string
    {
        return __('vendra-order::navigation.order_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-order::attributes.order_settings'))
                    ->description(__('vendra-order::attributes.order_settings_description'))
                    ->schema([
                        TextInput::make('number_prefix')
                            ->label(__('vendra-order::attributes.number_prefix'))
                            ->helperText(__('vendra-order::attributes.number_prefix_hint'))
                            ->alphaNum()
                            ->maxLength(10)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
