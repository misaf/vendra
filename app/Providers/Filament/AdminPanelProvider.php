<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\EditProfile;
use App\Filament\Admin\Pages\Auth\Login;
use App\Settings\GeneralSettings;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraTenant\Models\Tenant;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

final class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->brandLogo(fn() => app()->environment('production')
                ? asset('images/' . Tenant::current()->slug . '.webp')
                : null)
            ->brandLogoHeight('10rem')
            ->brandName(fn(GeneralSettings $generalSettings) => $generalSettings->site_title)
            ->colors([
                'primary' => Color::Gray
            ])
            ->databaseNotifications()
            ->databaseTransactions()
            ->defaultThemeMode(ThemeMode::Dark)
            ->discoverClusters(app_path('Filament/Admin/Clusters'), 'App\\Filament\\Admin\\Clusters')
            ->discoverPages(app_path('Filament/Admin/Pages'), 'App\\Filament\\Admin\\Pages')
            ->discoverResources(app_path('Filament/Admin/Resources'), 'App\\Filament\\Admin\\Resources')
            ->discoverWidgets(app_path('Filament/Admin/Widgets'), 'App\\Filament\\Admin\\Widgets')
            // ->font('yekan', 'https://cdn.font-store.ir/yekan.css', LocalFontProvider::class)
            ->globalSearchFieldKeyBindingSuffix()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->homeUrl('/')
            ->login(Login::class)
            ->sidebarFullyCollapsibleOnDesktop()
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
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->persistentMiddleware([
                EnsureValidTenantSession::class,
                NeedsTenant::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label(fn(): string => __('navigation.user_management'))->icon('heroicon-o-users')->collapsed(),
                NavigationGroup::make()->label(fn(): string => __('navigation.billing_management'))->icon('heroicon-o-credit-card')->collapsed(),
                NavigationGroup::make()->label(fn(): string => __('navigation.transaction_management'))->icon('heroicon-o-users')->collapsed(),
                NavigationGroup::make()->label(fn(): string => __('navigation.content_management'))->icon('heroicon-o-users')->collapsed(),
                NavigationGroup::make()->label(fn(): string => __('navigation.report_management'))->icon('heroicon-o-bug-ant')->collapsed(),
                NavigationGroup::make()->label(fn(): string => __('navigation.setting_management'))->icon('heroicon-o-cog-6-tooth')->collapsed(),
            ])
            ->maxContentWidth(Width::Full)
            ->path('/admin')
            ->profile(EditProfile::class, isSimple: false)
            ->spa(hasPrefetching: true)
            ->strictAuthorization()
            ->tenant(Tenant::class)
            ->unsavedChangesAlerts()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins([
                SpatieTranslatablePlugin::make()
                    ->defaultLocales(['en', 'fa']),

                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->users(function (): array {
                        return Config::string('auth.providers.users.model')::query()->role(Config::string('vendra-permission.super_admin_role'))->pluck('email', 'username')->toArray();
                    })
                    ->modelClass(Config::string('auth.providers.users.model')),
            ]);
    }
}
