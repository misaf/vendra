<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Filament\Pages;

use BackedEnum;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Misaf\VendraInquiry\Settings\InquirySettings;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;

final class ManageInquirySettings extends SystemSettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?int $navigationSort = NavigationPriority::InquirySettings->value;

    protected static string $settings = InquirySettings::class;

    protected static ?string $slug = 'inquiry-settings';

    public static function getNavigationLabel(): string
    {
        return __('vendra-inquiry::navigation.inquiry_settings');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('vendra-inquiry::attributes.inquiry_settings'))
                    ->description(__('vendra-inquiry::attributes.inquiry_settings_description'))
                    ->schema([
                        TagsInput::make('occasions')
                            ->label(__('vendra-inquiry::attributes.occasions'))
                            ->helperText(__('vendra-inquiry::attributes.occasions_hint'))
                            ->nestedRecursiveRules(['alpha_dash:ascii', 'max:64'])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
