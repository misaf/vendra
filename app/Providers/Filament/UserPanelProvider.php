<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\User\Pages\Auth\EditProfile;
use App\Filament\User\Pages\Auth\PasswordReset\RequestPasswordReset;
use App\Filament\User\Pages\Auth\PasswordReset\ResetPassword;
use App\Filament\User\Pages\Auth\Register;
use App\Http\Middleware\PreventRequestByCountryMiddleware;
use App\Settings\GeneralSettings;
use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Misaf\VendraUser\Models\User;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

final class UserPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('user')
            ->path('user')
            ->login()
            ->authMiddleware([Authenticate::class])
            ->brandLogoHeight('10rem')
            // ->brandName(fn(GeneralSettings $generalSettings) => $generalSettings->site_title . ' User')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->databaseNotifications()
            ->databaseTransactions()
            ->defaultThemeMode(ThemeMode::Dark)
            ->discoverClusters(app_path('Filament/User/Clusters'), 'App\\Filament\\User\\Clusters')
            ->discoverPages(app_path('Filament/User/Pages'), 'App\\Filament\\User\\Pages')
            ->discoverResources(app_path('Filament/User/Resources'), 'App\\Filament\\User\\Resources')
            ->discoverWidgets(app_path('Filament/User/Widgets'), 'App\\Filament\\User\\Widgets')
            ->emailVerification(isRequired: true)
            // ->font('yekan', 'https://cdn.font-store.ir/yekan.css', LocalFontProvider::class)
            ->globalSearchFieldKeyBindingSuffix()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->homeUrl('/')
            ->maxContentWidth(Width::ScreenTwoExtraLarge)
            ->middleware([
                NeedsTenant::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                EnsureValidTenantSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // PreventRequestByCountryMiddleware::class,
            ])
            ->persistentMiddleware([
                NeedsTenant::class,
            ])
            // ->navigationGroups([
            //     NavigationGroup::make()
            //         ->collapsed(false)
            //         ->icon('heroicon-o-banknotes')
            //         ->label(fn(): string => __('navigation.my_billing')),
            // ])
            // ->navigationItems([
            //     NavigationItem::make()
            //         ->icon('heroicon-o-user')
            //         ->label(fn(): string => __('navigation.edit_profile'))
            //         ->sort(6)
            //         ->url(fn(): string => EditProfile::getUrl()),
            // ])
            ->passwordReset(RequestPasswordReset::class, ResetPassword::class)
            ->profile(EditProfile::class, isSimple: false)
            ->registration(Register::class)
            ->spa()
            ->unsavedChangesAlerts()
            // ->userMenuItems([
            //     'profile' => MenuItem::make()
            //         ->label(fn(): string => filament()->auth()->user()->username),
            // ])
            // ->renderHook(
            //     PanelsRenderHook::SIDEBAR_NAV_START,
            //     fn() => Blade::render(
            //         '<livewire:affiliate-commission-earned-balance :$userId />',
            //         ['userId' => filament()->auth()->user()->getAuthIdentifier()],
            //     ),
            // )
            // ->plugins([
            //     FilamentDeveloperLoginsPlugin::make()
            //         ->enabled(app()->environment('local'))
            //         ->users(fn() => User::query()->role(['super-admin'])->pluck('email', 'username')->toArray())
            //         ->modelClass(User::class),
            // ]);
        ;
    }
}
