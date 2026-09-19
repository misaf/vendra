<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Settings\GeneralSettings;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Misaf\VendraSupport\Filament\Clusters\SystemCluster;
use Misaf\VendraSupport\Filament\Navigation\NavigationPriority;

final class ManageGeneralSettings extends SettingsPage
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SystemCluster::class;

    protected static ?int $navigationSort = NavigationPriority::GeneralSettings->value;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $slug = 'configurations';

    public static function getModelLabel(): string
    {
        return __('page.configuration');
    }

    public static function getNavigationLabel(): string
    {
        return __('page.configuration');
    }

    public static function getPluralModelLabel(): string
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

    public function getTitle(): string
    {
        return __('page.configuration');
    }
}
