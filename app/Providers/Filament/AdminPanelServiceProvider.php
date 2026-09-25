<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Auth\Login;
use App\Settings\GeneralSettings;
use Filament\Contracts\Plugin;
use Filament\FontProviders\SpatieGoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraLocalization\Http\Middleware\SetLocale;
use Misaf\VendraReseller\Http\Middleware\AddResellerToRequestJobContext;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Http\Middleware\AddPanelToRequestJobContext;
use Misaf\VendraTenant\Http\Middleware\EnsureAdminDomain;
use Misaf\VendraUser\Filament\Pages\Auth\EditProfile;
use Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession;
use Spatie\Multitenancy\Http\Middleware\NeedsTenant;

final class AdminPanelServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        Schema::configureUsing(
            static fn (Schema $schema): Schema => $schema->extraAttributes(
                static fn (): array => $schema->getKey() === 'headerWidgets'
                    ? ['class' => 'max-md:order-last']
                    : [],
                merge: true,
            ),
        );

        return $panel
            ->default()
            ->id('admin')
            ->brandLogo(fn () => asset('images/vendra-logo.svg'))
            ->brandLogoHeight('2rem')
            ->brandName(fn (): ?string => $this->brandName())
            ->darkModeBrandLogo(fn () => asset('images/vendra-logo-dark.svg'))
            ->databaseNotifications()
            ->databaseTransactions()
            ->discoverClusters(app_path('Filament/Admin/Clusters'), 'App\\Filament\\Admin\\Clusters')
            ->discoverPages(app_path('Filament/Admin/Pages'), 'App\\Filament\\Admin\\Pages')
            ->discoverResources(app_path('Filament/Admin/Resources'), 'App\\Filament\\Admin\\Resources')
            ->globalSearchFieldKeyBindingSuffix()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->homeUrl('/dashboard')
            ->authGuard('web')
            ->authPasswordBroker('users')
            ->login(Login::class)
            ->maxContentWidth(Width::Full)
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('14rem')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                EnsureAdminDomain::class,
                NeedsTenant::class,
                EnsureValidTenantSession::class,
                AddResellerToRequestJobContext::class,
                AddPanelToRequestJobContext::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->font(
                fn (): string => app()->isLocale('fa') ? 'Vazirmatn' : 'Google',
                provider: SpatieGoogleFontProvider::class,
            )
            ->path('')
            ->profile(EditProfile::class)
            ->spa(hasPrefetching: true)
            ->strictAuthorization()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->plugins($this->plugins());
    }

    /**
     * Name the panel after the store, in the admin's language when it has one.
     */
    private function brandName(): ?string
    {
        $storeName = resolve(TenantResolver::class)->current()?->getAttribute('name');

        return resolve(GeneralSettings::class)->nameFor(app()->getLocale())
            ?? (is_string($storeName) ? $storeName : null);
    }

    /**
     * @return array<int, Plugin>
     */
    private function plugins(): array
    {
        return [
            SpatieTranslatablePlugin::make()
                ->defaultLocales(Locales::configured()),
        ];
    }
}
