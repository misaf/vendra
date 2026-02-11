<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Clusters\Settings\SettingsCluster;
use Filament\Clusters\Cluster;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Misaf\Perfectmoney\Settings\PerfectMoneySettings;

final class ManagePerfectMoneySettings extends SettingsPage
{
    /**
     * @var class-string<Cluster>|null
     */
    protected static ?string $cluster = SettingsCluster::class;

    protected static ?int $navigationSort = 1;

    protected static string $settings = PerfectMoneySettings::class;

    protected static ?string $slug = 'perfectmoney';

    public static function getModelLabel(): string
    {
        return __('پرفکت مانی');
    }

    public static function getNavigationGroup(): string
    {
        return __('پرداخت ها');
    }

    public static function getNavigationLabel(): string
    {
        return __('پرفکت مانی');
    }

    public static function getPluralModelLabel(): string
    {
        return __('پرفکت مانی');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('deposit')
                            ->label(__('خرید'))
                            ->schema([
                                TextInput::make('usd_x_toman')
                                    ->columnSpanFull()
                                    ->extraAttributes(['dir' => 'ltr'])
                                    ->label(__('مبلغ'))
                                    ->numeric()
                                    ->required(),
                            ]),

                        Tab::make('withdrawal')
                            ->label(__('فروش'))
                            ->schema([
                                TextInput::make('toman_x_usd')
                                    ->columnSpanFull()
                                    ->extraAttributes(['dir' => 'ltr'])
                                    ->label(__('مبلغ'))
                                    ->numeric()
                                    ->required(),

                                Fieldset::make('Label')
                                    ->label(__('اعمال محدودیت'))
                                    ->schema([
                                        TextInput::make('withdrawal_limit_count')
                                            ->columnSpanFull()
                                            ->extraAttributes(['dir' => 'ltr'])
                                            ->label(__('تعداد'))
                                            ->numeric()
                                            ->required(),

                                        TextInput::make('withdrawal_limit_hours')
                                            ->columnSpanFull()
                                            ->extraAttributes(['dir' => 'ltr'])
                                            ->label(__('مدت زمان ( ساعت )'))
                                            ->numeric()
                                            ->required(),
                                    ]),
                            ]),
                    ])
                    ->persistTabInQueryString('perfectmoney-settings-tab')
                    ->columnSpanFull(),
            ]);
    }

    public function getTitle(): string|Htmlable
    {
        return __('پرفکت مانی');
    }
}
