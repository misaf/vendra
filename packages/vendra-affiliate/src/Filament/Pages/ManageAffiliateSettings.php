<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Settings\AffiliateSettings;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;

final class ManageAffiliateSettings extends SystemSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static ?int $navigationSort = NavigationPriority::AffiliateSettings->value;

    protected static string $settings = AffiliateSettings::class;

    protected static ?string $slug = 'affiliate-settings';

    public static function getNavigationLabel(): string
    {
        return __('vendra-affiliate::navigation.affiliate_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-affiliate::attributes.settings_commissions'))
                    ->schema([
                        TextInput::make('commission_percent')
                            ->label(__('vendra-affiliate::attributes.default_commission_percent'))
                            ->helperText(__('vendra-affiliate::attributes.default_commission_percent_hint'))
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        TextInput::make('signup_bounty')
                            ->label(__('vendra-affiliate::attributes.default_signup_bounty'))
                            ->helperText(__('vendra-affiliate::attributes.default_signup_bounty_hint'))
                            ->integer()
                            ->minValue(0)
                            ->required(),

                        Toggle::make('auto_approve_commissions')
                            ->label(__('vendra-affiliate::attributes.auto_approve_commissions'))
                            ->helperText(__('vendra-affiliate::attributes.auto_approve_commissions_hint'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('vendra-affiliate::attributes.settings_conversions'))
                    ->description(__('vendra-affiliate::attributes.settings_conversions_description'))
                    ->schema([
                        Toggle::make('deposit_conversions')
                            ->label(ConversionTypeEnum::Deposit->getLabel()),

                        Toggle::make('signup_conversions')
                            ->label(ConversionTypeEnum::Signup->getLabel()),

                        Toggle::make('checkout_conversions')
                            ->label(ConversionTypeEnum::Checkout->getLabel()),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(__('vendra-affiliate::attributes.settings_attribution'))
                    ->schema([
                        TextInput::make('cookie_ttl_days')
                            ->label(__('vendra-affiliate::attributes.cookie_ttl_days'))
                            ->helperText(__('vendra-affiliate::attributes.cookie_ttl_days_hint'))
                            ->integer()
                            ->minValue(1)
                            ->maxValue(365)
                            ->suffix(__('vendra-affiliate::attributes.days'))
                            ->required(),

                        TextInput::make('redirect_url')
                            ->label(__('vendra-affiliate::attributes.redirect_url'))
                            ->helperText(__('vendra-affiliate::attributes.redirect_url_hint'))
                            ->maxLength(2048)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('vendra-affiliate::attributes.settings_payouts'))
                    ->schema([
                        TextInput::make('payout_minimum')
                            ->label(__('vendra-affiliate::attributes.payout_minimum'))
                            ->helperText(__('vendra-affiliate::attributes.payout_minimum_hint'))
                            ->integer()
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
