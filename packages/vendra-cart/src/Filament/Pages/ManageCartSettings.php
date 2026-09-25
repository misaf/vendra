<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraCart\Settings\CartSettings;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;

final class ManageCartSettings extends SystemSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = NavigationPriority::CartSettings->value;

    protected static string $settings = CartSettings::class;

    protected static ?string $slug = 'cart-settings';

    public static function getNavigationLabel(): string
    {
        return __('vendra-cart::navigation.cart_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-cart::attributes.cart_settings'))
                    ->description(__('vendra-cart::attributes.cart_settings_description'))
                    ->schema([
                        TextInput::make('expires_after_days')
                            ->label(__('vendra-cart::attributes.expires_after_days'))
                            ->helperText(__('vendra-cart::attributes.expires_after_days_hint'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(365),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
