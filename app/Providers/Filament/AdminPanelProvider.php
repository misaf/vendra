<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin;
use App\Models\Tenant\Tenant;
use App\Settings\GlobalSetting;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\PanelSwitch\PanelSwitch;
use Filament\FontProviders\LocalFontProvider;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Assets\Css;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables;
use Filament\Widgets;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class AdminPanelProvider extends PanelProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Support\Facades\FilamentAsset::register([
            Css::make('example-external-stylesheet', '//cdn.font-store.ir/yekan.css'),
        ]);

        \BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch->locales(['fa', 'en'])
                ->visible(outsidePanels: true);
        });

        // PanelSwitch::configureUsing(function (PanelSwitch $panelSwitch): void {
        //     $panelSwitch->modalHeading('Available Panels')->visible(fn(): bool => auth()->user()->hasAnyRole([
        //         'admin',
        //         'general_manager',
        //         'super_admin',
        //     ]))->renderHook('panels::global-search.after');
        // });

        \Filament\Tables\Table::configureUsing(function (Tables\Table $table): void {
            $table->paginationPageOptions([10, 25, 50]);
        });

        \Filament\Tables\Actions\ViewAction::configureUsing(function (Tables\Actions\ViewAction $viewAction): void {
            $viewAction->button();
        });

        \Filament\Tables\Actions\EditAction::configureUsing(function (Tables\Actions\EditAction $editAction): void {
            $editAction->button();
        });

        \Filament\Tables\Actions\DeleteAction::configureUsing(function (Tables\Actions\DeleteAction $deleteAction): void {
            $deleteAction->button();
        });
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            // ->brandLogo(asset(app(GlobalSetting::class)->site_sidebar_logo_light))
            // ->brandName(app(GlobalSetting::class)->site_title)
            // ->darkModeBrandLogo(asset(app(GlobalSetting::class)->site_sidebar_logo_dark))
            ->brandLogoHeight('2rem')
            ->default()
            ->id('admin')
            ->login()
            ->path(LaravelLocalization::setLocale() . '/admin')
            ->profile(isSimple: false)
            ->font(
                'yekan',
                url: asset('css/fonts.css'),
                provider: LocalFontProvider::class,
            )
            ->colors([
                'primary' => Color::Amber,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.product_management'))
                    ->icon('heroicon-o-building-storefront')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.blog_management'))
                    ->icon('heroicon-o-presentation-chart-line')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.currency_management'))
                    ->icon('heroicon-o-currency-dollar')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.faq_management'))
                    ->icon('heroicon-o-question-mark-circle')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.user_management'))
                    ->icon('heroicon-o-users')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.geographical_management'))
                    ->icon('heroicon-o-globe-europe-africa')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.report_management'))
                    ->icon('heroicon-o-bug-ant')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label(fn(): string => __('navigation.setting_management'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->discoverClusters(in: app_path('Filament/Admin/Clusters'), for: 'App\\Filament\\Admin\\Clusters')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->plugin(SpatieLaravelTranslatablePlugin::make()->defaultLocales(['fa', 'en']))
            ->pages([
                \Filament\Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                \Illuminate\Cookie\Middleware\EncryptCookies::class,
                \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
                \Illuminate\Session\Middleware\StartSession::class,
                \Illuminate\Session\Middleware\AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
                \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Routing\Middleware\SubstituteBindings::class,
                \Filament\Http\Middleware\DisableBladeIconComponents::class,
                \Filament\Http\Middleware\DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \Filament\Http\Middleware\Authenticate::class,
            ])
            ->databaseNotifications()
            ->databaseTransactions()
            ->globalSearchKeyBindings(keyBindings: ['command+k', 'ctrl+k'])
            ->spa()
            ->viteTheme(theme: 'resources/css/filament/admin/theme.css');
            // ->tenant(
            //     model: Tenant::class,
            // );
            // ->tenantDomain(domain: '{tenant:domain}')
            // ->tenantProfile(page: \App\Filament\Admin\Pages\Tenancy\EditTenantProfile::class)
            // ->tenantRegistration(page: \App\Filament\Admin\Pages\Tenancy\RegisterTenant::class)
            // ->tenantMenuItems([
            //     'register' => \Filament\Navigation\MenuItem::make()->label('Register new team'),
            // ]);
    }
}
