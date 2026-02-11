<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Clusters\Settings\SettingsCluster;
use App\Settings\GeneralSettings;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Misaf\Tenant\Models\Tenant;

final class ManageGeneralSettings extends SettingsPage
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = 1;

    protected static string $settings = GeneralSettings::class;

    protected static ?string $slug = 'configurations';

    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     $data['tenant_id'] = Tenant::current()->id;

    //     return $data;
    // }

    public static function getModelLabel(): string
    {
        return __('page.configuration');
    }

    public static function getNavigationGroup(): string
    {
        return __('page.setting');
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
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('general_settings')
                            ->label(__('setting.general'))
                            ->schema([
                                TextInput::make('site_title')
                                    ->columnSpanFull()
                                    ->label(__('form.title'))
                                    ->required(),

                                Textarea::make('site_description')
                                    ->columnSpanFull()
                                    ->label(__('form.description'))
                                    ->rows(5),
                            ]),

                        Tab::make('global_authentication')
                            ->translateLabel(true)
                            ->label('setting.authentication')
                            ->schema([
                                // Toggle::make('user_email_verification')
                                //     ->columnSpanFull()
                                //     ->inline(false)
                                //     ->label(__('auth.email_verification'))
                                //     ->rules('required'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('page.configuration');
    }
}
