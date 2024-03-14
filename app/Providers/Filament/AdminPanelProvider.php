<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Settings\GlobalSetting;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\SpatieLaravelTranslatablePlugin;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ->brandLogo(asset(app(GlobalSetting::class)->site_sidebar_logo_light))
            ->brandLogoHeight('2rem')
            // ->brandName(app(GlobalSetting::class)->site_title)
            // ->darkModeBrandLogo(asset(app(GlobalSetting::class)->site_sidebar_logo_dark))
            ->default()
            ->id('admin')
            ->login()
            ->path(LaravelLocalization::setLocale() . '/admin')
            ->profile()
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
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->discoverClusters(in: app_path('Filament/Admin/Clusters'), for: 'App\\Filament\\Admin\\Clusters')
            ->plugin(SpatieLaravelTranslatablePlugin::make()->defaultLocales(['fa', 'en']))
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Admin\Widgets\StatOverview::class,
                \App\Filament\Admin\Widgets\LatestUserTable::class,
                \App\Filament\Admin\Widgets\LatestUserProfileDocumentTable::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\SanitizeResponse::class,
                \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            // ->databaseNotificationsPolling('30s')
            // ->authPasswordBroker('admin')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa();
        ;
    }
}
