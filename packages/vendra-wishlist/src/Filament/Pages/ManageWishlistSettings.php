<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;
use Misaf\VendraWishlist\Settings\WishlistSettings;

final class ManageWishlistSettings extends SystemSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?int $navigationSort = NavigationPriority::WishlistSettings->value;

    protected static string $settings = WishlistSettings::class;

    protected static ?string $slug = 'wishlist-settings';

    public static function getNavigationLabel(): string
    {
        return __('vendra-wishlist::navigation.wishlist_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-wishlist::attributes.wishlist_settings'))
                    ->description(__('vendra-wishlist::attributes.wishlist_settings_description'))
                    ->schema([
                        TextInput::make('default_name')
                            ->label(__('vendra-wishlist::attributes.default_name'))
                            ->helperText(__('vendra-wishlist::attributes.default_name_hint'))
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
