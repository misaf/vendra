<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Settings\GeneralSettings;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;
use Misaf\VendraSupport\Filament\Pages\SystemSettingsPage;

final class ManageGeneralSettings extends SystemSettingsPage
{
    protected static ?int $navigationSort = NavigationPriority::GeneralSettings->value;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $slug = 'configurations';

    public static function getNavigationLabel(): string
    {
        return __('page.configuration');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('setting.general'))
                    ->schema([
                        TextInput::make('site_title')
                            ->columnSpanFull()
                            ->label(__('form.title'))
                            ->maxLength(255)
                            ->required(),

                        Textarea::make('site_description')
                            ->columnSpanFull()
                            ->label(__('form.description'))
                            ->maxLength(1000)
                            ->rows(5),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
